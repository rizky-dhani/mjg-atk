<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockRequestResource\Pages;
use App\Filament\Resources\StockRequestResource\RelationManagers;
use App\Models\StockRequest;
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

class StockRequestResource extends Resource
{
    protected static ?string $model = StockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Stock Requests';
    protected static ?string $navigationParentItem = 'Office Stationery';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(StockRequest::TYPE_INCREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Request Items')
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
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
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
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => StockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        StockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => StockRequest::STATUS_PENDING,
                        'success' => [StockRequest::STATUS_APPROVED_BY_HEAD, StockRequest::STATUS_APPROVED_BY_IPC, StockRequest::STATUS_APPROVED_BY_IPC_HEAD, StockRequest::STATUS_DELIVERED, StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, StockRequest::STATUS_APPROVED_BY_GA_ADMIN, StockRequest::STATUS_APPROVED_BY_GA_HEAD, StockRequest::STATUS_COMPLETED],
                        'danger' => [StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC, StockRequest::STATUS_REJECTED_BY_IPC_HEAD, StockRequest::STATUS_REJECTED_BY_GA_ADMIN, StockRequest::STATUS_REJECTED_BY_GA_HEAD],
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        StockRequest::STATUS_PENDING => 'Pending',
                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        StockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        StockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        StockRequest::STATUS_DELIVERED => 'Delivered',
                        StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        StockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        StockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        StockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                        StockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                        StockRequest::STATUS_COMPLETED => 'Completed',
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
                        StockRequest::TYPE_INCREASE => 'Stock Increase',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        StockRequest::STATUS_PENDING => 'Pending',
                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        StockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        StockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        StockRequest::STATUS_DELIVERED => 'Delivered',
                        StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        StockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        StockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        StockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                        StockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                        StockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === StockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === StockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => StockRequest::STATUS_APPROVED_BY_HEAD,
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
                        $record->status === StockRequest::STATUS_PENDING && 
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
                            'status' => StockRequest::STATUS_REJECTED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
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
                        $record->status === StockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => StockRequest::STATUS_APPROVED_BY_IPC,
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
                        $record->status === StockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => StockRequest::STATUS_REJECTED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()->timezone('Asia/Jakarta'),
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
                            'status' => StockRequest::STATUS_APPROVED_BY_IPC_HEAD,
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
                            'status' => StockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta'),
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
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        $record->update([
                            'status' => StockRequest::STATUS_DELIVERED,
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
                    ->visible(fn ($record) => 
                        $record->needsStockAdjustmentApproval() &&
                        auth()->user()->hasRole('Staff') &&
                        auth()->user()->division?->initial === 'IPC'
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('item.name')
                                    ->label('Item')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Requested Quantity')
                                    ->disabled(),
                                Forms\Components\TextInput::make('adjusted_quantity')
                                    ->label('Adjusted Quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->default(fn ($state, $record) => $record->quantity),
                            ])
                            ->columns(3)
                            ->disabled(fn ($record) => !$record->needsStockAdjustmentApproval())
                    ])
                    ->action(function ($record, array $data) {
                        // Validate adjusted quantities against maximum limits
                        $validationErrors = [];
                        foreach ($record->items as $item) {
                            $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                            
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
                                ->body(implode('\n', $validationErrors))
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Save adjusted quantities
                        foreach ($record->items as $item) {
                            $item->adjusted_quantity = $item->adjusted_quantity ?? $item->quantity;
                            $item->save();
                        }
                        
                        $record->update([
                            'status' => StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock adjusted and approved successfully')
                            ->success()
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
                            'status' => StockRequest::STATUS_APPROVED_BY_GA_ADMIN,
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
                            'status' => StockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ga_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsGaHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        // Update stock levels with adjusted quantities
                        foreach ($record->items as $item) {
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
                            'status' => StockRequest::STATUS_COMPLETED,
                            'approval_ga_head_id' => auth()->user()->id,
                            'approval_ga_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by GA Head and stock updated successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ga_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsGaHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
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
                            'status' => StockRequest::STATUS_REJECTED_BY_GA_HEAD,
                            'approval_ga_head_id' => auth()->user()->id,
                            'approval_ga_head_at' => now()->timezone('Asia/Jakarta'),
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
        if (auth()->user()->division?->name === 'IPC') {
            $query->orderByDesc('request_number');
        }else{
            $query->orderByDesc('request_number')->where('division_id', auth()->user()->division_id);
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
                                        StockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        StockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes'),
                            ]),
                    ])
                    ->columns(1),
                Infolists\Components\Section::make('Stock Request Status')
                    ->schema([
                        Infolists\Components\Grid::make(6)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        StockRequest::STATUS_PENDING => 'Pending',
                                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        StockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                                        StockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                                        StockRequest::STATUS_DELIVERED => 'Delivered',
                                        StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                                        StockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                        StockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                        StockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                                        StockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                                        StockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        StockRequest::STATUS_PENDING => 'warning',
                                        StockRequest::STATUS_APPROVED_BY_HEAD, StockRequest::STATUS_APPROVED_BY_IPC, StockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'success',
                                        StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC, StockRequest::STATUS_REJECTED_BY_IPC_HEAD, StockRequest::STATUS_REJECTED_BY_GA_ADMIN, StockRequest::STATUS_REJECTED_BY_GA_HEAD => 'danger',
                                        StockRequest::STATUS_DELIVERED, StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, StockRequest::STATUS_APPROVED_BY_GA_ADMIN, StockRequest::STATUS_APPROVED_BY_GA_HEAD, StockRequest::STATUS_COMPLETED => 'success',
                                        default => 'secondary',
                                    })
                                    ->columnSpan(6),
                                Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Head Approve')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('ipcStaff.name')
                                    ->label('IPC Approve')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('ipcHead.name')
                                    ->label('IPC Head Approve')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ipc_head_at')
                                    ->label('IPC Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_stock_adjustment_by.name')
                                    ->label('Stock Adjustment Approved By')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_stock_adjustment_at')
                                    ->label('Stock Adjustment Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('gaAdmin.name')
                                    ->label('GA Admin Approve')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ga_admin_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('gaHead.name')
                                    ->label('GA Head Approve')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ga_head_at')
                                    ->label('GA Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC, StockRequest::STATUS_REJECTED_BY_IPC_HEAD, StockRequest::STATUS_REJECTED_BY_GA_ADMIN, StockRequest::STATUS_REJECTED_BY_GA_HEAD]))
                                    ->columnSpan(6),
                            ]),
                    ])
                    ->columns(1),
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
                    ->columns(1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockRequests::route('/'),
            'view' => Pages\ViewStockRequest::route('/{record}'),
        ];
    }
}
