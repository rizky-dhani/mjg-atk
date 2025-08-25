<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;
use App\Filament\Resources\OfficeStationeryStockRequestResource\RelationManagers;
use App\Models\OfficeStationeryStockRequest;
use App\Models\CompanyDivision;
use App\Models\item;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class OfficeStationeryStockRequestResource extends Resource
{
    protected static ?string $model = OfficeStationeryStockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Stock Requests';
    protected static ?string $navigationParentItem = 'Office Stationery';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
                Forms\Components\Section::make('Office Stationery Stock Request Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('item.category', 'name')
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('category_id');
                                        if (!$categoryId) {
                                            return [];
                                        }
                                        return \App\Models\OfficeStationeryItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }
                                        
                                        $setting = \App\Models\DivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return 'No inventory limit set for this item';
                                        }
                                        
                                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        $maxLimit = $setting->max_limit;
                                        $availableSpace = $maxLimit - $currentStock;
                                        
                                        return "Current stock: {$currentStock}, Max limit: {$maxLimit}, Available space: {$availableSpace}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) {
                                            return;
                                        }
                                        
                                        $setting = \App\Models\DivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return;
                                        }
                                        
                                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        $maxLimit = $setting->max_limit;
                                        $availableSpace = $maxLimit - $currentStock;
                                        
                                        if ($state > $availableSpace) {
                                            // Reset to available space
                                            $set('quantity', $availableSpace);
                                            
                                            // Show notification to user
                                            \Filament\Notifications\Notification::make()
                                                ->title('Quantity adjusted')
                                                ->body("The requested quantity has been adjusted to the maximum available space: {$availableSpace}")
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
                                                
                                                $setting = \App\Models\DivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                    ->where('item_id', $itemId)
                                                    ->first();
                                                    
                                                if (!$setting) {
                                                    return;
                                                }
                                                
                                                $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                                    ->where('item_id', $itemId)
                                                    ->first();
                                                    
                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                $maxLimit = $setting->max_limit;
                                                $availableSpace = $maxLimit - $currentStock;
                                                
                                                if ($value > $availableSpace) {
                                                    $fail("The requested quantity ({$value}) exceeds the available space ({$availableSpace}) for this item.");
                                                }
                                            };
                                        },
                                    ]),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->autosize(),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                Forms\Components\Section::make('Office Stationery Stock Request Information (Optional)')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(OfficeStationeryStockRequest::TYPE_INCREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => OfficeStationeryStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_DELIVERED, OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved by IPC Head (Post Adjustment)',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected by IPC Head (Post Adjustment)',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Stock Increase',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved by IPC Head (Post Adjustment)',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected by IPC Head (Post Adjustment)',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by IPC successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by IPC Head successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('deliver')
                    ->label('Mark as Delivered')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn ($record) => 
                        $record->canBeDelivered() &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_DELIVERED,
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request marked as delivered')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('adjust_and_approve_stock')
                    ->label('Adjust & Approve Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Stock Request Adjustment')
                    ->modalSubheading('Are you sure to make the adjustment to the Stock Request?')
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => 
                        $record->needsStockAdjustmentApproval() &&
                        auth()->user()->hasRole('Admin') &&
                        auth()->user()->division?->initial === 'IPC'
                    )
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
                            Forms\Components\Repeater::make('items')
                                ->schema([
                                    Forms\Components\TextInput::make('item_name')
                                        ->label('Item')
                                        ->disabled()
                                        ->extraInputAttributes(['class' => 'whitespace-normal']),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Requested Quantity')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('adjusted_quantity')
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
                            
                            // Get current stock and maximum limit
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            $divisionInventorySetting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            // Calculate new stock level
                            $currentStock = $officeStationeryStockPerDivision->current_stock ?? 0;
                            $newStock = $currentStock + $adjustedQuantity;
                            
                            // Check against maximum limit
                            if ($divisionInventorySetting && $newStock > $divisionInventorySetting->max_limit) {
                                $validationErrors[] = "Item {$item->item->name} would exceed the maximum limit of {$divisionInventorySetting->max_limit} units (new total would be {$newStock} units).";
                            }
                        }
                        
                        // If there are validation errors, display them and stop the process
                        if (!empty($validationErrors)) {
                            Notification::make()
                                ->title('Stock adjustment exceeds maximum limits')
                                ->body(implode("", $validationErrors))
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
                            'approval_stock_adjustment_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock adjusted and approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_second_ipc_head')
                    ->label('Approve as IPC Head (Post Adjustment)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsSecondIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by IPC Head (Post Adjustment) successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject_as_second_ipc_head')
                    ->label('Reject as IPC Head (Post Adjustment)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsSecondIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected by IPC Head (Post Adjustment) successfully')
                            ->warning()
                            ->send();
                    }),
            
            Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsGaAdminApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by GA Admin successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsGaAdminApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsHcgHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'HCG' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        // Update stock levels with adjusted quantities
                        foreach ($record->items as $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                            
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
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
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD,
                            'approval_ga_head_id' => auth()->user()->id,
                            'approval_ga_head_at' => now()->timezone('Asia/Jakarta'),
                            // Automatically mark as delivered
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by HCG Head, stock updated, and marked as delivered successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsHcgHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'HCG' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_ga_head_id' => auth()->user()->id,
                            'rejection_ga_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter based on user role        
        $user = auth()->user();
        if($user->division?->initial === 'GA' && $user->hasRole('Admin')){
            $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD)->orderByDesc('created_at')->orderByDesc('request_number');
        }elseif($user->division?->initial === 'IPC' && $user->hasRole('Admin')){
            $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)->orderByDesc('created_at')->orderByDesc('request_number');
        }elseif($user->division?->initial === 'IPC' && $user->hasRole('Head')){
            $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC)->orderByDesc('created_at')->orderByDesc('request_number');
        }elseif($user->division?->initial === 'HCG' && $user->hasRole('Head')){
            $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN)->orderByDesc('created_at')->orderByDesc('request_number');
        }else{
            $query->where('division_id', $user->division_id)->orderByDesc('created_at')->orderByDesc('request_number');
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolists\Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Stock Request Detail')
                    ->schema([
                        Infolists\Components\Grid::make(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('request_number')
                                    ->label('Request Number'),
                                Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->persistCollapsed()
                    ->id('stock-request-detail'),
                    
                Infolists\Components\Section::make('Stock Request Status')
                    ->schema([
                        Infolists\Components\Grid::make(6)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved by IPC Head (Post Adjustment)',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected by IPC Head (Post Adjustment)',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_DELIVERED, OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                                        default => 'secondary',
                                    })
                                    ->columnSpan(6),
                                Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionHead.name')
                                    ->label('Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_head_at')
                                    ->label('Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('ipcAdmin.name')
                                    ->label('IPC Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpc.name')
                                    ->label('IPC Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_at')
                                    ->label('IPC Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('ipcHead.name')
                                    ->label('IPC Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_head_at')
                                    ->label('IPC Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpcHead.name')
                                    ->label('IPC Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_head_at')
                                    ->label('IPC Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approvalStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Approved By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('approval_stock_adjustment_at')
                                    ->label('Stock Adjustment Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejectionStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Rejected By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejection_stock_adjustment_at')
                                    ->label('Stock Adjustment Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('gaAdmin.name')
                                    ->label('GA Admin Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_admin_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                                    ->label('GA Admin Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                                    ->label('GA Admin Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('gaHead.name')
                                    ->label('GA Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_head_at')
                                    ->label('GA Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaHead.name')
                                    ->label('GA Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_head_at')
                                    ->label('GA Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD]))
                                    ->columnSpan(6),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-request-status'),

                Infolists\Components\Section::make('Stock Request Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(5)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Name'),
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Requested Quantity'),
                                        Infolists\Components\TextEntry::make('adjusted_quantity')
                                            ->label('Adjusted Quantity')
                                            ->visible(fn ($record) => $record->adjusted_quantity !== null),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('-'),
                                    ]),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->persistCollapsed()
                    ->id('stock-request-items'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficeStationeryStockRequests::route('/'),
            'view' => Pages\ViewOfficeStationeryStockRequest::route('/{record}'),
        ];
    }
}
