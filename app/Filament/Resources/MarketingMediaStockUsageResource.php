<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaStockUsageResource\Pages;
use App\Models\MarketingMediaStockUsage;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class MarketingMediaStockUsageResource extends Resource
{
    protected static ?string $model = MarketingMediaStockUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Stock Usages';
    protected static ?string $navigationParentItem = 'Marketing Media';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Usage Items')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MarketingMediaStockUsage::TYPE_DECREASE),
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('marketing_media_id')
                                    ->label('Marketing Media')
                                    ->relationship('marketingMedia', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
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
                                                
                                                // Get the marketing_media_id for this repeater item
                                                $mediaId = data_get($livewire, "data.items.{$index}.marketing_media_id");
                                                
                                                if (!$mediaId || !$value) {
                                                    return;
                                                }
                                                
                                                // Get division_id from the form or from the record
                                                $divisionId = null;
                                                if (request()->routeIs('filament.dashboard.resources.marketing-media-stock-usages.create')) {
                                                    $divisionId = auth()->user()->division_id;
                                                } else {
                                                    $record = MarketingMediaStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                                }
                                                
                                                $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', $divisionId)
                                                    ->where('marketing_media_id', $mediaId)
                                                    ->first();
                                                    
                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                
                                                if ($value > $currentStock) {
                                                    $fail("The requested quantity ({$value}) exceeds the available stock ({$currentStock}) for this item.");
                                                }
                                            };
                                        },
                                    ]),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $mediaId = $get('marketing_media_id');
                                        if (!$mediaId) {
                                            return '';
                                        }
                                        
                                        // Get division_id from the form or from the record
                                        $divisionId = null;
                                        if (request()->routeIs('filament.dashboard.resources.marketing-media-stock-usages.create')) {
                                            $divisionId = auth()->user()->division_id;
                                        } else {
                                            $record = MarketingMediaStockUsage::find(request()->route('record'));
                                            $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                        }
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', $divisionId)
                                            ->where('marketing_media_id', $mediaId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        
                                        return "Current stock: {$currentStock}";
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
                                                
                                                // Get the marketing_media_id for this repeater item
                                                $mediaId = data_get($livewire, "data.items.{$index}.marketing_media_id");
                                                
                                                if (!$mediaId || !$value) {
                                                    return;
                                                }
                                                
                                                // Get division_id from the form or from the record
                                                $divisionId = null;
                                                if (request()->routeIs('filament.dashboard.resources.marketing-media-stock-usages.create')) {
                                                    $divisionId = auth()->user()->division_id;
                                                } else {
                                                    $record = MarketingMediaStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                                }
                                                
                                                $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', $divisionId)
                                                    ->where('marketing_media_id', $mediaId)
                                                    ->first();
                                                    
                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                
                                                if ($value > $currentStock) {
                                                    $fail("The requested quantity ({$value}) exceeds the available stock ({$currentStock}) for this item.");
                                                }
                                            };
                                        },
                                    ]),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->autosize(),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                Forms\Components\Section::make('Stock Usage Information (Optional)')
                    ->schema([
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
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MarketingMediaStockUsage::TYPE_DECREASE => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('status')
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
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => $record->status === MarketingMediaStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === MarketingMediaStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve')
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
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Usage approved successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject')
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
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Usage rejected successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Usage approved successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Usage rejected successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_as_mkt_head')
                    ->label('Approve & Process Stock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                        (auth()->user()->hasRole('Head') && auth()->user()->division && auth()->user()->division->name === 'Marketing Support')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD,
                            'approval_mkt_head_id' => auth()->user()->id,
                            'approval_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        // Process the stock usage
                        $record->processStockUsage();
                        
                        Notification::make()
                            ->title('Usage approved and stock processed successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_mkt_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                        (auth()->user()->hasRole('Head') && auth()->user()->division && auth()->user()->division->name === 'Marketing Support')
                    )
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD,
                            'rejection_mkt_head_id' => auth()->user()->id,
                            'rejection_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Stock Usage rejected successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            $query->where('division_id', $user->division_id)->orderByDesc('usage_number');
        }
        
        return $query;
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
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-detail'),
                Infolists\Components\Section::make('Stock Usage Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
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
                            })
                            ->columnSpan(6),
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
                        Infolists\Components\TextEntry::make('marketingSupportHead.name')
                            ->label('Marketing Support Head Approve')
                            ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('approval_mkt_head_at')
                            ->label('Marketing Support Head Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejectionMarketingSupportHead.name')
                            ->label('Marketing Support Head Rejection')
                            ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_mkt_head_at')
                            ->label('Marketing Support Head Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_mkt_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($record) => in_array($record->status, [MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD]))
                            ->columnSpan(6),
                        
                    ])
                    ->columns(6)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-status'),
                    
                Infolists\Components\Section::make('Stock Request Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('marketingMedia.name')
                                            ->label('Name'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Quantity'),
                                        Infolists\Components\TextEntry::make('previous_stock')
                                            ->label('Previous Stock')
                                            ->visible(fn ($record) => $record->previous_stock !== null),
                                        Infolists\Components\TextEntry::make('new_stock')
                                            ->label('New Stock')
                                            ->visible(fn ($record) => $record->new_stock !== null),
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaStockUsages::route('/'),
            'view' => Pages\ViewMarketingMediaStockUsage::route('/{record}'),
        ];
    }
}