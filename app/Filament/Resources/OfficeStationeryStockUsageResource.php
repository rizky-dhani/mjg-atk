<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CompanyDivision;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\OfficeStationeryStockUsage;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;
use App\Filament\Resources\OfficeStationeryStockUsageResource\RelationManagers;

class OfficeStationeryStockUsageResource extends Resource
{
    protected static ?string $model = OfficeStationeryStockUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Pengeluaran ATK';
    protected static ?string $modelLabel = 'Pengeluaran ATK';
    protected static ?string $pluralModelLabel = 'Pengeluaran ATK';
    protected static ?int $navigationSort = 5;
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(OfficeStationeryStockUsage::TYPE_DECREASE),
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->addable(false)
                            ->cloneable()
                            ->extraItemActions([
                                Action::make('add_new_after')
                                    ->icon('heroicon-m-plus')
                                    ->color('primary')
                                    ->action(function (array $arguments, Repeater $component) {
                                        $items = $component->getState();
                                        $currentKey = $arguments['item'];
                                        
                                        // Create new item
                                        $newItem = [];
                                        $newKey = uniqid();
                                        
                                        // Insert after current item
                                        $newItems = [];
                                        foreach ($items as $key => $item) {
                                            $newItems[$key] = $item;
                                            if ($key === $currentKey) {
                                                $newItems[$newKey] = $newItem;
                                            }
                                        }
                                        
                                        $component->state($newItems);
                                    }),
                            ])
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
                                                
                                                // Get division_id from the form or from the record
                                                $divisionId = null;
                                                if (request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.create')) {
                                                    $divisionId = auth()->user()->division_id;
                                                } else {
                                                    $record = OfficeStationeryStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                                }
                                                
                                                $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $divisionId)
                                                    ->where('item_id', $itemId)
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
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }
                                        
                                        // Get division_id from the form or from the record
                                        $divisionId = null;
                                        if (request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.create')) {
                                            $divisionId = auth()->user()->division_id;
                                        } else {
                                            $record = OfficeStationeryStockUsage::find(request()->route('record'));
                                            $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                        }
                                        
                                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $divisionId)
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
                                                
                                                // Get division_id from the form or from the record
                                                $divisionId = null;
                                                if (request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.create')) {
                                                    $divisionId = auth()->user()->division_id;
                                                } else {
                                                    $record = OfficeStationeryStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                                }
                                                
                                                $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $divisionId)
                                                    ->where('item_id', $itemId)
                                                    ->first();
                                                    
                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                
                                                // For decrease type, check if quantity exceeds available stock
                                                // For increase type, no validation needed
                                                $usageType = data_get($livewire, 'data.type') ?? OfficeStationeryStockUsage::TYPE_DECREASE;
                                                if ($usageType === OfficeStationeryStockUsage::TYPE_DECREASE && $value > $currentStock) {
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
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                Forms\Components\Section::make('Pengeluaran ATK Information (Optional)')
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
            ->modifyQueryUsing(function($query){
                // Filter based on user role
                $user = auth()->user();
                
                // GA Admins see only requests with status from Approved by Head to Completed (approval workflow)
                if ($user->division?->initial === 'GA' && $user->hasRole('Admin') || $user->division?->initial === 'HCG' && $user->hasRole('Head')) {
                    // Filter records to show only status from Approved by Head to Completed
                    $statuses = [
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        OfficeStationeryStockUsage::STATUS_COMPLETED,
                    ];
                    
                    $query->whereIn('status', $statuses)->orderByDesc('created_at')->orderByDesc('usage_number');
                }
                // All Admin users (including GA division Admins) see all requests from their division, regardless of status
                elseif ($user->hasRole('Admin')) {
                    $query->where('division_id', $user->division_id)->orderByDesc('created_at')->orderByDesc('usage_number');
                }
                // All other users only see records from their own division
                else {
                    $query->where('division_id', $user->division_id)->orderByDesc('created_at')->orderByDesc('usage_number');
                }
                
                return $query;
            })
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
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'Decrease',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'warning',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockUsage::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
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
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'Decrease',
                    ]),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di approve!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_PENDING && 
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
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di reject!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di approve!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD && 
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
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di reject!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                        (auth()->user()->hasRole('Head') && auth()->user()->division && auth()->user()->division->initial === 'HCG')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_COMPLETED,
                            'approval_hcg_head_id' => auth()->user()->id,
                            'approval_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        // Process the Pengeluaran ATK
                        $record->processStockUsage();
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di approve dan stok item berhasil diperbaharui!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
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
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_hcg_head_id' => auth()->user()->id,
                            'rejection_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di reject!')
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
                                OfficeStationeryStockUsage::TYPE_DECREASE => 'danger',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                OfficeStationeryStockUsage::TYPE_DECREASE => 'Decrease',
                                default => ucfirst($state),
                            }),
                        Infolists\Components\TextEntry::make('notes'),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-detail'),
                Infolists\Components\Section::make('Pengeluaran ATK Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                OfficeStationeryStockUsage::STATUS_PENDING => 'warning',
                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockUsage::STATUS_COMPLETED => 'success',
                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                                OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
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
                            ->visible(fn ($record) => in_array($record->status, [OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD]))
                            ->columnSpan(6),
                        
                    ])
                    ->columns(6)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-status'),
                    
                Infolists\Components\Section::make('Pengeluaran ATK Items')
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficeStationeryStockUsages::route('/'),
            'my-divisions' => Pages\MyDivisionOfficeStationeryStockUsage::route('/my-divisions'),
            'usage-list' => Pages\UsageListOfficeStationeryStockUsage::route('/usage-list'),
            'view' => Pages\ViewOfficeStationeryStockUsage::route('/{record}'),
        ];
    }
}
