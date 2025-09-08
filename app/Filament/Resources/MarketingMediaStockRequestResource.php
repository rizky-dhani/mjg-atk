<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaStockRequestResource\Pages;
use App\Filament\Resources\MarketingMediaStockRequestResource\RelationManagers;
use App\Models\MarketingMediaStockRequest;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaStockRequestResource extends Resource
{
    protected static ?string $model = MarketingMediaStockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?string $navigationLabel = 'Pemasukan Media Cetak';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MarketingMediaStockRequest::TYPE_INCREASE),
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
                                        return \App\Models\MarketingMediaItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }
                                        
                                        $setting = \App\Models\MarketingMediaDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return 'No inventory limit set for this item';
                                        }
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
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
                                        
                                        $setting = \App\Models\MarketingMediaDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return;
                                        }
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
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
                                    }),
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
                        'primary' => MarketingMediaStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_DELIVERED, MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                        MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
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
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        MarketingMediaStockRequest::TYPE_INCREASE => 'Stock Increase',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                        MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === MarketingMediaStockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve (Head)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject (Head)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc')
                    ->label('Approve (IPC)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak approved by IPC successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ipc')
                    ->label('Reject (IPC)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak rejected by IPC successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc_head')
                    ->label('Approve (IPC Head)')
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak approved by IPC Head successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ipc_head')
                    ->label('Reject (IPC Head)')
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak rejected by IPC Head successfully')
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
                        // Update stock levels
                        foreach ($record->items as $item) {
                            $officeStationeryStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->lockForUpdate()
                                ->first();
                                
                            if ($officeStationeryStockPerDivision) {
                                // Store previous stock
                                $item->previous_stock = $officeStationeryStockPerDivision->current_stock;
                                
                                // Update stock
                                $officeStationeryStockPerDivision->increment('current_stock', $item->quantity);
                                
                                // Store new stock
                                $item->new_stock = $officeStationeryStockPerDivision->current_stock;
                                $item->save();
                            }
                        }
                        
                        $record->update([
                            'status' => $record->isIncrease() ? MarketingMediaStockRequest::STATUS_COMPLETED : MarketingMediaStockRequest::STATUS_DELIVERED,
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak marked as delivered and stock updated')
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
                            $officeStationeryStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            $divisionInventorySetting = \App\Models\MarketingMediaDivisionInventorySetting::where('division_id', $record->division_id)
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
                            \Filament\Notifications\Notification::make()
                                ->title('Stock adjustment exceeds maximum limits')
                                ->body(implode("\n", $validationErrors))
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Stock adjusted and approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve (GA Admin)')
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak approved by GA Admin successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject (GA Admin)')
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak rejected by GA Admin successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_marketing_head')
                    ->label('Approve (Marketing Support Head)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsMarketingHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'MKS' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        // Update stock levels with adjusted quantities
                        foreach ($record->items as $item) {
                            $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                            
                            $officeStationeryStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('division_id', $record->division_id)
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
                            'status' => MarketingMediaStockRequest::STATUS_COMPLETED,
                            'approval_marketing_head_id' => auth()->user()->id,
                            'approval_marketing_head_at' => now(),
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak approved by Marketing Support Head and stock updated successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_marketing_head')
                    ->label('Reject (Marketing Support Head)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsMarketingHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'MKS' &&
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                            'approval_marketing_head_id' => auth()->user()->id,
                            'approval_marketing_head_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pemasukan Media Cetak rejected by Marketing Support Head successfully')
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
        if ($user->hasRole(['Admin']) || $user->hasRole(['Head'])) {
            // For Marketing divisions, only show their own requests
            if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
                $query->where('division_id', $user->division_id);
            }
            // For IPC, GA, HCG divisions, they can see all requests for approval process
            else if ($user->division && 
                     ($user->division->initial === 'IPC' || 
                      $user->division->initial === 'GA' || 
                      $user->division->initial === 'HCG')) {
                // These divisions can see all requests for approval
            }
            // For other divisions, only show their own requests
            else {
                $query->where('division_id', $user->division_id);
            }
        }
        
        return $query;
    }
    
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow Marketing divisions to view their own requests
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Staff']);
        }
        
        // Allow IPC and GA divisions to view all requests for approval process
        if ($user->division && 
            ($user->division->initial === 'IPC' || 
             $user->division->initial === 'GA')) {
            return $user->hasRole(['Admin', 'Head', 'Staff']);
        }
        
        // Hide from users who don't belong to any Marketing divisions
        return false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Only allow Marketing divisions to create requests
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Staff']);
        }
        
        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow editing if user belongs to the same division as the record and is from a Marketing division
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Staff']) && 
                   $user->division_id === $record->division_id;
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow deleting if user belongs to the same division as the record and is from a Marketing division
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin']) && 
                   $user->division_id === $record->division_id;
        }
        
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Pemasukan Media Cetak Detail')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(5)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('request_number')
                                    ->label('Request Number'),
                                \Filament\Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                \Filament\Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                                \Filament\Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        MarketingMediaStockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        MarketingMediaStockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes'),
                            ]),
                    ])
                    ->columns(1),
                \Filament\Infolists\Components\Section::make('Pemasukan Media Cetak Status')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(5)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                                        MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                                        MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'success',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                                        MarketingMediaStockRequest::STATUS_DELIVERED, MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                                        default => 'secondary',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionHead.name')
                                    ->label('Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_head_at')
                                    ->label('Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('ipcAdmin.name')
                                    ->label('IPC Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionIpc.name')
                                    ->label('IPC Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_ipc_at')
                                    ->label('IPC Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('ipcHead.name')
                                    ->label('IPC Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_ipc_head_at')
                                    ->label('IPC Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionIpcHead.name')
                                    ->label('IPC Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_ipc_head_at')
                                    ->label('IPC Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approvalStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Approved By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_stock_adjustment_at')
                                    ->label('Stock Adjustment Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Rejected By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_stock_adjustment_at')
                                    ->label('Stock Adjustment Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('gaAdmin.name')
                                    ->label('GA Admin Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_ga_admin_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                                    ->label('GA Admin Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                                    ->label('GA Admin Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('gaHead.name')
                                    ->label('GA Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('approval_ga_head_at')
                                    ->label('GA Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejectionGaHead.name')
                                    ->label('GA Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_ga_head_at')
                                    ->label('GA Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                \Filament\Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD]))
                                    ->columnSpan(6),
                            ]),
                    ])
                    ->columns(1),
                \Filament\Infolists\Components\Section::make('Pemasukan Media Cetak Items')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                \Filament\Infolists\Components\Grid::make(4)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('item.name')
                                            ->label('Name'),
                                        \Filament\Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                        \Filament\Infolists\Components\TextEntry::make('quantity')
                                            ->label('Requested Quantity'),
                                        \Filament\Infolists\Components\TextEntry::make('adjusted_quantity')
                                            ->label('Adjusted Quantity')
                                            ->visible(fn ($record) => $record->adjusted_quantity !== null),
                                        \Filament\Infolists\Components\TextEntry::make('notes')
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
            'index' => Pages\ListMarketingMediaStockRequests::route('/'),
            'view' => Pages\ViewMarketingMediaStockRequest::route('/{record}'),
        ];
    }
}