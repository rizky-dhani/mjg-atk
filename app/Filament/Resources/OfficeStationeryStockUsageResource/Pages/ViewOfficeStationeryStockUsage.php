<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use Filament\Actions;
use App\Helpers\RequestStatusChecker;
use App\Helpers\UserRoleChecker;
use Filament\Forms\Components\Grid;
use App\Models\OfficeStationeryItem;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockRequest;
use Filament\Forms\Components\Actions\Action;
use App\Models\OfficeStationeryStockPerDivision;
use App\Models\OfficeStationeryDivisionInventorySetting;
use App\Filament\Resources\OfficeStationeryStockUsageResource;

class ViewOfficeStationeryStockUsage extends ViewRecord
{
    protected static string $resource = OfficeStationeryStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->disabled(fn($record) => $record->status !== OfficeStationeryStockUsage::STATUS_PENDING)
                ->visible(fn($record) => UserRoleChecker::getCreatorDivisionId($record)),
            Actions\EditAction::make('resubmit_request')
                ->label('Resubmit')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->visible(fn($record) => RequestStatusChecker::canResubmitOfficeStationeryStockUsage($record) && UserRoleChecker::getRequesterId($record))
                ->form([
                    Grid::make(1)->schema([
                        Section::make('Rejection Information')
                            ->schema([
                                TextInput::make('rejected_by')
                                    ->label('Rejected By')
                                    ->disabled()
                                    ->default(function ($record) {
                                        if (RequestStatusChecker::atkStockUsageRejectedByDivisionHead($record)) {
                                            return $record->rejectionHead->name ?? '';
                                        } elseif (RequestStatusChecker::atkStockUsageRejectedByGaAdmin($record)) {
                                            return $record->rejectionGaAdmin->name ?? '';
                                        } elseif (RequestStatusChecker::atkStockUsageRejectedByHcgHead($record)) {
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
                            ->visible(fn ($record) => RequestStatusChecker::stockUsageIsRejected($record)),
                        Repeater::make('items')
                            ->addable(false)
                            ->relationship()
                            ->cloneable()
                            ->extraItemActions([
                                Action::make('add_new_after')
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
                                Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('item.category', 'name')
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
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

                                        return "Current: {$currentStock}";
                                    })
                                    ->live()
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

                                                // Get division_id from the record
                                                $record = OfficeStationeryStockUsage::find(request()->route('record'));
                                                $divisionId = $record ? $record->division_id : auth()->user()->division_id;

                                                $stock = OfficeStationeryStockPerDivision::where('division_id', $divisionId)
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;

                                                if ($value > $currentStock) {
                                                    $fail("Kuantitas yang diminta ({$value}) melebihi batas maksimal yaitu ({$currentStock}) untuk item ini.");
                                                }
                                            };
                                        },
                                    ]),
                                Textarea::make('notes')
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->autosize(),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addable(false)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                    Section::make('Pengeluaran ATK Information (Optional)')
                        ->schema([
                            Hidden::make('type')
                                ->default(OfficeStationeryStockUsage::TYPE_DECREASE),
                            Textarea::make('notes')
                                ->maxLength(65535)
                                ->columnSpanFull()
                        ])
                        ->columns(2),
                ])
                ->action(function ($record, array $data) {
                    // Update the record with new data
                    $record->update($data);

                    // Reset status to pending and clear rejection information
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_PENDING,
                        'rejection_head_id' => null,
                        'rejection_head_at' => null,
                        'rejection_ga_admin_id' => null,
                        'rejection_ga_admin_at' => null,
                        'rejection_hcg_head_id' => null,
                        'rejection_hcg_head_at' => null,
                        'rejection_reason' => null,
                    ]);

                    Notification::make()
                        ->title('Pengeluaran ATK berhasil diresubmit!')
                        ->success()
                        ->send();
                }),
            // Division Head Approval/Rejection Actions
            Actions\Action::make('approve_as_head')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromDivisionHead($record))
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                        'approval_head_id' => Auth::user()->id,
                        'approval_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasil di-approve!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_head')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromDivisionHead($record))
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD,
                        'rejection_head_id' => Auth::user()->id,
                        'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasil di-reject!')
                        ->success()
                        ->send();
                }),

            // GA Admin Approval/Rejection Actions
            Actions\Action::make('approve_as_ga_admin')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromGaAdmin($record))
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        'approval_ga_admin_id' => Auth::user()->id,
                        'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasil di-approve!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_ga_admin')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromGaAdmin($record))
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                        'rejection_ga_admin_id' => Auth::user()->id,
                        'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasil di-reject!')
                        ->success()
                        ->send();
                }),

            // Supervisor/Head Marketing Approval/Rejection Actions
            Actions\Action::make('approve_as_hcg_head')
                ->label('Approve & Process Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromHcgHead($record))
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD,
                        'approval_hcg_head_id' => Auth::user()->id,
                        'approval_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    // Process the Pengeluaran ATK
                    $record->processStockUsage();

                    // $record->update([
                    //     'status' => OfficeStationeryStockUsage::STATUS_COMPLETED,
                    // ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasi di-approve dan stok diperbaharui!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_hcg_head')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromHcgHead($record))
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD,
                        'rejection_hcg_head_id' => Auth::user()->id,
                        'rejection_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran ATK berhasil di-reject!')
                        ->success()
                        ->send();
                }),
        ];
    }
}
