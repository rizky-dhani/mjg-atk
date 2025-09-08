<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaStockUsageResource\Pages;
use App\Filament\Resources\MarketingMediaStockUsageResource\RelationManagers;
use App\Models\MarketingMediaStockUsage;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaStockUsageResource extends Resource
{
    protected static ?string $model = MarketingMediaStockUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?string $navigationLabel = 'Pengeluaran Media Cetak';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Usage Information')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MarketingMediaStockUsage::TYPE_DECREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Usage Items')
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
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        
                                        return "Current stock: {$currentStock}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) {
                                            return;
                                        }
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        
                                        if ($state > $currentStock) {
                                            // Reset to available stock
                                            $set('quantity', $currentStock);
                                            
                                            // Show notification to user
                                            \Filament\Notifications\Notification::make()
                                                ->title('Quantity adjusted')
                                                ->body("The requested quantity has been adjusted to the available stock: {$currentStock}")
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
                Tables\Columns\TextColumn::make('usage_number')
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
                        'danger' => MarketingMediaStockUsage::TYPE_DECREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MarketingMediaStockUsage::STATUS_PENDING => 'warning',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD, MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockUsage::STATUS_COMPLETED => 'success',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
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
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Stock Decrease',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === MarketingMediaStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve (Head)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pengeluaran Media Cetak approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject (Head)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_PENDING && 
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
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pengeluaran Media Cetak rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve (GA Admin)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
                        $record->isDecrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Admin')
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
                                $officeStationeryStockPerDivision->decrement('current_stock', $item->quantity);
                                
                                // Store new stock
                                $item->new_stock = $officeStationeryStockPerDivision->current_stock;
                                $item->save();
                            }
                        }
                        
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_COMPLETED,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pengeluaran Media Cetak approved by GA Admin and stock updated')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject (GA Admin)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
                        $record->isDecrease() && auth()->user()->division?->initial === 'GA' &&
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
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Pengeluaran Media Cetak rejected by GA Admin successfully')
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
            $query->where('status', MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD);
        }elseif($user->division?->initial === 'MKS' && $user->hasRole('Head')){
            $query->where('status', MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN);
        }else{
            // For Marketing divisions, only show their own usages
            if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
                $query->where('division_id', $user->division_id);
            }
            // For IPC, GA divisions, they can see all usages for approval process
            else if ($user->division && 
                     ($user->division->initial === 'IPC' || 
                      $user->division->initial === 'GA')) {
                // These divisions can see all usages for approval
            }
            // For other divisions, only show their own usages
            else {
                $query->where('division_id', $user->division_id);
            }
            $query->orderByDesc('usage_number');
        }
        
        return $query;
    }
    
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow Marketing divisions to view their own usages
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Staff']);
        }
        
        // Allow IPC, GA divisions to view all usages for approval process
        if ($user->division && 
            ($user->division->initial === 'IPC' || $user->division->initial === 'GA')) {
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
        
        // Only allow Marketing divisions to create usages
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Usage Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('usage_number'),
                        Infolists\Components\TextEntry::make('requester.name')
                            ->label('Requested By'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                MarketingMediaStockUsage::TYPE_DECREASE => 'danger',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                                default => ucfirst($state),
                            }),
                        Infolists\Components\TextEntry::make('notes'),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-detail'),
                Infolists\Components\Section::make('Pengeluaran Media Cetak Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                MarketingMediaStockUsage::STATUS_PENDING => 'warning',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD, MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'success',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'danger',
                                MarketingMediaStockUsage::STATUS_COMPLETED => 'success',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
                                default => ucfirst(str_replace('_', ' ', $state)),
                            }),
                        Infolists\Components\TextEntry::make('divisionHead.name')
                            ->label('Head Approve')
                            ->visible(fn ($record) => $record->approval_head_id !== null),
                        Infolists\Components\TextEntry::make('approval_head_at')
                            ->label('Head Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_head_id !== null),
                        Infolists\Components\TextEntry::make('rejectionHead.name')
                            ->label('Head Rejection')
                            ->visible(fn ($record) => $record->rejection_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_head_at')
                            ->label('Head Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_head_id !== null),
                        Infolists\Components\TextEntry::make('gaAdmin.name')
                            ->label('GA Admin Approve')
                            ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('approval_ga_admin_at')
                            ->label('GA Admin Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                            ->label('GA Admin Rejection')
                            ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                            ->label('GA Admin Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('supervisorMarketing.name')
                            ->label('HCG Head Approve')
                            ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('approval_mkt_head_at')
                            ->label('HCG Head Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejectionSupervisorMarketing.name')
                            ->label('HCG Head Rejection')
                            ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_mkt_head_at')
                            ->label('HCG Head Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($record) => in_array($record->status, [MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD]))
                            ->columnSpan(6),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-status'),
                
                Infolists\Components\Section::make('Pengeluaran Media Cetak Items')
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
                                            ->label('Quantity'),
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
            'index' => Pages\ListMarketingMediaStockUsages::route('/'),
            'view' => Pages\ViewMarketingMediaStockUsage::route('/{record}'),
        ];
    }
}