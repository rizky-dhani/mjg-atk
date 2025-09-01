<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaStockRequestResource\Pages;
use App\Filament\Resources\MarketingMediaStockRequestResource\RelationManagers;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaCategory;
use App\Models\CompanyDivision;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class MarketingMediaStockRequestResource extends Resource
{
    protected static ?string $model = MarketingMediaStockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Penambahan Barang';
    protected static ?string $navigationGroup = 'Media Marketing';
    protected static ?string $navigationParentItem = 'Media Cetak';
    protected static ?int $navigationSort = 2;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Marketing Media Stock Request Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('marketingMediaItem.category', 'name')
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
                                Forms\Components\Select::make('marketing_media_id')
                                    ->label('Marketing Media')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('category_id');
                                        if (!$categoryId) {
                                            return [];
                                        }
                                        return MarketingMediaItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
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
                Forms\Components\Section::make('Marketing Media Stock Request Information (Optional)')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MarketingMediaStockRequest::TYPE_INCREASE),
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
                        'primary' => MarketingMediaStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\TextColumn::make('status')
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
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by MKT Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by MKT Head',
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
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        MarketingMediaStockRequest::TYPE_INCREASE => 'Stock Increase',
                    ]),
                SelectFilter::make('status')
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
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by MKT Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by MKT Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                        'in_progress' => 'In Progress',
                        'rejected' => 'Rejected'
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === MarketingMediaStockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === MarketingMediaStockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve')
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
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_PENDING && 
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
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
                        $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request rejected successfully')
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request rejected successfully')
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
                            'status' => MarketingMediaStockRequest::STATUS_DELIVERED,
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
                                'item_name' => $item->marketingMediaItem->name ?? '',
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock adjusted and approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_second_ipc_head')
                    ->label('Approve (Post Adjustment)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsStockAdjustmentApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved (Post Adjustment) successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject_as_second_ipc_head')
                    ->label('Reject (Post Adjustment)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsStockAdjustmentApproval() && 
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected (Post Adjustment) successfully')
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
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_admin_ga_id' => auth()->user()->id,
                            'approval_admin_ga_at' => now()->timezone('Asia/Jakarta')
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_admin_ga_id' => auth()->user()->id,
                            'rejection_admin_ga_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_mkt_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->needsMarketingSupportHeadApproval() && 
                        $record->isIncrease() && 
                        (auth()->user()->division?->initial === 'MBB' || auth()->user()->division?->initial === 'MHO' || 
                         auth()->user()->division?->initial === 'MPC' || auth()->user()->division?->initial === 'MPH' || 
                         auth()->user()->division?->initial === 'MKS') &&
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
                            
                            $marketingMediaStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('marketing_media_id', $item->marketing_media_id)
                                ->where('division_id', $record->division_id)
                                ->lockForUpdate()
                                ->first();
                                
                            if ($marketingMediaStockPerDivision) {
                                // Store previous stock
                                $item->previous_stock = $marketingMediaStockPerDivision->current_stock;
                                
                                // Update stock
                                $marketingMediaStockPerDivision->increment('current_stock', $adjustedQuantity);
                                
                                // Store new stock
                                $item->new_stock = $marketingMediaStockPerDivision->current_stock;
                                $item->save();
                            }
                        }
                        
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_COMPLETED,
                            'approval_mkt_head_id' => auth()->user()->id,
                            'approval_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                            // Automatically mark as delivered
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request approved and stock updated successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_mkt_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->needsMarketingSupportHeadApproval() && 
                        $record->isIncrease() && 
                        (auth()->user()->division?->initial === 'MBB' || auth()->user()->division?->initial === 'MHO' || 
                         auth()->user()->division?->initial === 'MPC' || auth()->user()->division?->initial === 'MPH' || 
                         auth()->user()->division?->initial === 'MKS') &&
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
                            'rejection_mkt_head_id' => auth()->user()->id,
                            'rejection_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request rejected successfully')
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
    
    public static function infolist(Infolist $infolist): Infolist
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
                                        MarketingMediaStockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        MarketingMediaStockRequest::TYPE_INCREASE => 'primary',
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
                                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                                        MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                                        MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved by IPC Head (Post Adjustment)',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected by IPC Head (Post Adjustment)',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by MKT Head',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by MKT Head',
                                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_DELIVERED, MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
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
                                    ->visible(fn ($record) => $record->approval_admin_ga_id !== null),
                                Infolists\Components\TextEntry::make('approval_admin_ga_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_admin_ga_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                                    ->label('GA Admin Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_admin_ga_id !== null),
                                Infolists\Components\TextEntry::make('rejection_admin_ga_at')
                                    ->label('GA Admin Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_admin_ga_id !== null),
                                Infolists\Components\TextEntry::make('marketingSupportHead.name')
                                    ->label('MKT Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_mkt_head_at')
                                    ->label('MKT Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionMarketingSupportHead.name')
                                    ->label('MKT Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_mkt_head_at')
                                    ->label('MKT Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD]))
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
                                        Infolists\Components\TextEntry::make('marketingMediaItem.name')
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter based on user role        
        $user = auth()->user();
        
        // IPC & HCG Admins & Heads can see all requests (for approval workflow)
        if (($user->division?->initial === 'IPC' && ($user->hasRole('Admin') || $user->hasRole('Head'))) ||
            ($user->division?->initial === 'GA' && ($user->hasRole('Admin'))) ||
            ($user->division?->initial === 'HCG' && ($user->hasRole('Admin') || $user->hasRole('Head')))) {
            // No additional filtering needed, show all requests
            $query->orderByDesc('created_at')->orderByDesc('request_number');
        }
        // Marketing Admins & Heads only see requests from their own division
        elseif (($user->division?->initial === 'MBB' || $user->division?->initial === 'MHO' || 
                 $user->division?->initial === 'MPC' || $user->division?->initial === 'MPH' || 
                 $user->division?->initial === 'MKS') && 
                ($user->hasRole('Admin') || $user->hasRole('Head'))) {
            $query->where('division_id', $user->division_id)->orderByDesc('created_at')->orderByDesc('request_number');
        }
        // All other Admin users (including GA) only see requests from their own division
        elseif ($user->hasRole('Admin')) {
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaStockRequests::route('/'),
            'view' => Pages\ViewMarketingMediaStockRequest::route('/{record}'),
        ];
    }
}