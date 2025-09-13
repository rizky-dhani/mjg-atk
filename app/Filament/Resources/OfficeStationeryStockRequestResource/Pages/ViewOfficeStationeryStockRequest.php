<?php

namespace App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use App\Models\OfficeStationeryItem;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockPerDivision;
use App\Models\OfficeStationeryDivisionInventorySetting;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\OfficeStationeryStockRequestResource;
use App\Helpers\RequestStatusChecker;
use App\Helpers\UserRoleChecker;

class ViewOfficeStationeryStockRequest extends ViewRecord
{
    protected static string $resource = OfficeStationeryStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->disabled(fn($record) => $record->status !== OfficeStationeryStockRequest::STATUS_PENDING)
                ->visible(fn($record) => $record->division_id === UserRoleChecker::getCurrentUserDivisionId() && UserRoleChecker::isDivisionAdmin()),
            Actions\EditAction::make('resubmit_request')
                ->label('Resubmit')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->visible(fn($record) => RequestStatusChecker::canResubmitOfficeStationeryStockRequest($record) && UserRoleChecker::isDivisionAdmin() && UserRoleChecker::canApproveInDivision($record))
                ->form([
                    Grid::make(1)->schema([
                        Repeater::make('items')
                            ->addable(false)
                            ->relationship()
                            ->cloneable()
                            ->extraItemActions([
                                FormAction::make('add_new_after')
                                    ->icon('heroicon-m-plus')
                                    ->color('primary')
                                    ->action(function (array $arguments, Repeater $component) {
                                        $state = $component->getState();
                                        $currentKey = $arguments['item'];

                                        $newKey = uniqid('item_');
                                        // Pre-populate with empty values for proper binding
                                        $newItem = [
                                            'category_id' => null,
                                            'item_id' => null,
                                            'quantity' => null,
                                            'notes' => null,
                                        ];

                                        // Insert at correct position
                                        $keys = array_keys($state);
                                        $currentIndex = array_search($currentKey, $keys);

                                        $newState = array_slice($state, 0, $currentIndex + 1, true) + [$newKey => $newItem] + array_slice($state, $currentIndex + 1, null, true);

                                        $component->state($newState);
                                    }),
                            ])
                            ->schema([
                                Select::make('category_id')->label('Category')->relationship('item.category', 'name')->searchable()->reactive()->preload(),
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('category_id');
                                        if (!$categoryId) {
                                            return [];
                                        }
                                        return OfficeStationeryItem::where('category_id', $categoryId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),
                                TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }

                                        $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        if (!$setting) {
                                            return 'No inventory limit set for this item';
                                        }

                                        $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        $maxLimit = $setting->max_limit;
                                        $availableSpace = $maxLimit - $currentStock;

                                        return "Current: {$currentStock} | Max: {$maxLimit} | Available: {$availableSpace}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) {
                                            return;
                                        }

                                        $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        if (!$setting) {
                                            return;
                                        }

                                        $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        $maxLimit = $setting->max_limit;
                                        $availableSpace = $maxLimit - $currentStock;

                                        if ($state > $availableSpace) {
                                            // Reset to available space
                                            $set('quantity', $availableSpace);

                                            // Show notification to user
                                            Notification::make()
                                                ->title('Kuantitas melebih batas maksimal')
                                                ->body("Kuantitas penyesuaian melebihi batas maksimal, maksimal kuantitas yang bisa diminta: {$availableSpace}")
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail, $livewire) {
                                                // Extract the repeater index from the attribute name
                                                // e.g., "data.items.0.quantity" -> index 0
                                                preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);
                                                $index = $matches[1] ?? null;

                                                if ($index === null) {
                                                    return;
                                                }

                                                // Get the item_id for this repeater item
                                                $itemId = data_get($livewire, "data.items.{$index}.item_id");

                                                if (!$itemId || !$value) {
                                                    return;
                                                }

                                                $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                if (!$setting) {
                                                    return;
                                                }

                                                $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                $maxLimit = $setting->max_limit;
                                                $availableSpace = $maxLimit - $currentStock;

                                                if ($value > $availableSpace) {
                                                    $fail("Kuantitas yang diminta ({$value}) melebihi batas maksimal yaitu ({$availableSpace}) untuk item ini.");
                                                }
                                            };
                                        },
                                    ]),
                                Textarea::make('notes')->maxLength(1000)->rows(1)->autosize(),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                    Section::make('Permintaan ATK Information (Optional)')
                        ->schema([
                            Hidden::make('type')->default(OfficeStationeryStockRequest::TYPE_INCREASE), Textarea::make('notes')->maxLength(65535)->columnSpanFull()
                        ])
                        ->columns(2),
                    Section::make('Rejection Information')
                        ->schema([
                            TextInput::make('rejected_by')
                                ->label('Rejected By')
                                ->disabled()
                                ->formatStateUsing(function ($record) {
                                    if (RequestStatusChecker::atkStockRequestRejectedByDivHead($record)) {
                                        return $record->rejectionHead->name ?? '';
                                    } elseif (RequestStatusChecker::atkStockRequestRejectedByIpcAdmin($record)) {
                                        return $record->rejectionIpc->name ?? '';
                                    } elseif (RequestStatusChecker::atkStockRequestRejectedByIpcHead($record)) {
                                        return $record->rejectionIpcHead->name ?? '';
                                    } elseif (RequestStatusChecker::atkStockRequestRejectedByGaAdmin($record)) {
                                        return $record->rejectionGaAdmin->name ?? '';
                                    } elseif (RequestStatusChecker::atkStockRequestRejectedByHcgHead($record)) {
                                        return $record->rejectionHcgHead->name ?? '';
                                    }
                                    return '';
                                }),
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->maxLength(65535)
                                ->disabled(),
                        ])
                        ->columns(2)
                        ->visible(fn ($record) => RequestStatusChecker::stockRequestIsRejected($record)),
                ])
                ->action(function ($record, array $data) {
                    // Update the record with new data
                    $record->update($data);

                    // Reset status to pending and clear rejection information
                    $record->update([
                        'status' => OfficeStationeryStockRequest::STATUS_PENDING,
                        'rejection_head_id' => null,
                        'rejection_head_at' => null,
                        'rejection_ipc_id' => null,
                        'rejection_ipc_at' => null,
                        'rejection_ipc_head_id' => null,
                        'rejection_ipc_head_at' => null,
                        'rejection_reason' => null,
                    ]);

                    Notification::make()->title('Permintaan ATK berhasil diresubmit!')->success()->send();
                }),
            Actions\DeleteAction::make()
            ->visible(fn ($record) => auth()->user()->id === $record->requested_by),
                // Approval Actions
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk approve Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk reject Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk approve Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcAdmin($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Permintaan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin reject Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcAdmin($record))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk approve Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Permintaan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin reject Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcHead($record))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
                
                    Action::make('adjust_and_approve_stock')
                    ->label('Adjust & Approve Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Penyesuaian Stok Permintaan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin melakukan penyesuaian stok pada Permintaan ATK ini?')
                    ->modalWidth(MaxWidth::Screen)
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedStockAdjustmentApprovalFromIpcAdmin($record))
                    ->requiresConfirmation()
                    ->form(function ($record) {
                        
                        // Pre-populate the items data
                        $itemsData = [];
                        foreach ($record->items as $item) {
                            $itemsData[] = [
                                'item_name' => $item->item->name ?? '',
                                'quantity' => $item->quantity ?? 0,
                                'adjusted_quantity' => $item->quantity ?? 0,
                            ];
                        }
                        
                        return [
                            Repeater::make('items')
                                ->schema([
                                    TextInput::make('item_name')
                                            ->label('Item')
                                        ->disabled()
                                        ->extraInputAttributes(['class' => 'whitespace-normal']),
                                    TextInput::make('quantity')
                                        ->label('Requested Quantity')
                                        ->disabled(),
                                    TextInput::make('adjusted_quantity')
                                        ->label('Adjusted Quantity')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required(),
                                ])
                                ->columns(3)
                                ->disabled(fn () => !$record->needsStockAdjustmentApproval())
                                ->required()
                                ->default($itemsData)
                        ];
                    })
                    ->action(function ($record, array $data) {
                        
                        // Validate adjusted quantities against maximum limits
                        $validationErrors = [];
                        foreach ($record->items as $index => $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            // Get adjusted quantity from form data
                            $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                            
                            // Get Current and maximum limit
                            $officeStationeryStockPerDivision = OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            $divisionInventorySetting = OfficeStationeryDivisionInventorySetting::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            // Calculate new stock level
                            $currentStock = $officeStationeryStockPerDivision->current_stock ?? 0;
                            $newStock = $currentStock + $adjustedQuantity;
                            
                            // Check against maximum limit
                            if ($divisionInventorySetting && $newStock > $divisionInventorySetting->max_limit) {
                                $validationErrors[] = "Item {$item->item->name} melebihi batas maksimal yaitu {$divisionInventorySetting->max_limit} units (kuantitas baru stok yaitu {$newStock} unit).";
                            }
                        }
                        
                        // If there are validation errors, display them and stop the process
                        if (!empty($validationErrors)) {
                            Notification::make()
                                ->title('Penyesuaian stok melebihi batas maksimum')
                                ->body(implode("\n", $validationErrors))
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Save adjusted quantities
                        foreach ($record->items as $index => $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            // Get adjusted quantity from form data
                            $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                            $item->adjusted_quantity = $adjustedQuantity;
                            $item->save();
                        }
                        
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stok Permintaan ATK berhasil disesuaikan!')
                            ->success()
                            ->send();
                    }),
            
            Action::make('approve_as_second_ipc_head')
                ->label('Approve ')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Permintaan ATK')
                ->modalSubheading('Apakah Anda yakin ingin approve Permintaan ATK ini setelah penyesuaian stok?')
                ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedSecondApprovalFromIpcHead($record))
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                        'approval_ipc_head_id' => auth()->user()->id,
                        'approval_ipc_head_at' => now('Asia/Jakarta')
                    ]);
                    
                    Notification::make()
                        ->title('Permintaan ATK berhasil di-approve!')
                        ->success()
                        ->send();
                }),

            Action::make('reject_as_second_ipc_head')
                ->label('Reject ')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Permintaan ATK')
                ->modalSubheading('Apakah Anda yakin ingin reject Permintaan ATK ini setelah penyesuaian stok?')
                ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedSecondApprovalFromIpcHead($record))
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                        'rejection_ipc_head_id' => auth()->user()->id,
                        'rejection_ipc_head_at' => now('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Permintaan ATK berhasil di-reject!')
                        ->warning()
                        ->send();
                }),
            
            Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk approve Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromGaAdmin($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Permintaan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin reject Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromGaAdmin($record))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Permintaan ATK')
                    ->modalSubheading('Apakah anda yakin untuk approve Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromHcgHead($record))
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        // Update stock levels with adjusted quantities
                        foreach ($record->items as $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                            
                            $officeStationeryStockPerDivision = OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->lockForUpdate()
                                ->first();
                                
                            if ($officeStationeryStockPerDivision) {
                                // Store previous stock
                                $item->previous_stock = $officeStationeryStockPerDivision->current_stock;
                                
                                // Update stock
                                $officeStationeryStockPerDivision->increment('current_stock', $adjustedQuantity);
                                
                                // Store new stock
                                $item->new_stock = $officeStationeryStockPerDivision->current_stock;
                                $item->save();
                            }
                        }
                        
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_COMPLETED,
                            'approval_hcg_head_id' => auth()->user()->id,
                            'approval_hcg_head_at' => now('Asia/Jakarta'),
                            // Automatically mark as delivered
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-approve dan stok diperbaharui!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Permintaan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin reject Permintaan ATK ini?')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromHcgHead($record))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_hcg_head_id' => auth()->user()->id,
                            'rejection_hcg_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Permintaan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
        ];
    }
}
