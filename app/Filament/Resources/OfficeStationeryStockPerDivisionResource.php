<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryStockPerDivisionResource\Pages;
use App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;
use App\Models\OfficeStationeryStockPerDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfficeStationeryStockPerDivisionResource extends Resource
{
    protected static ?string $model = OfficeStationeryStockPerDivision::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Office Stationery';
    protected static ?string $modelLabel = 'Office Stationery';
    protected static ?string $pluralModelLabel = 'Office Stationeries';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('item_id')
                    ->relationship('item', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('current_stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) =>
                $query->where('division_id', auth()->user()->division_id)
                    ->orderBy('category_id', 'asc'))
            ->columns([
                Tables\Columns\TextColumn::make('division.initial')
                    ->label('Division')
                    ->visible(fn() => auth()->user()->division?->name === 'IPC' || auth()->user()->division?->name === 'General Affairs' )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('item.category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_limit')
                    ->label('Max Limit')
                    ->getStateUsing(function ($record) {
                        $setting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        return $setting ? $setting->max_limit : '-';
                    })
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        $setting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        
                        if (!$setting) {
                            return 'No limit set';
                        }
                        
                        if ($record->current_stock > $setting->max_limit) {
                            return 'Over limit';
                        } elseif ($record->current_stock == $setting->max_limit) {
                            return 'At limit';
                        } else {
                            return 'Within limit';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Over limit' => 'danger',
                        'At limit' => 'warning',
                        'Within limit' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('item.unit_of_measure')
                    ->label('UOM')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division_id')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('item_id')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                Infolists\Components\Section::make('Office Stationery Item Details')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division')
                                    ->icon('heroicon-o-building-office-2'),
                                
                                Infolists\Components\TextEntry::make('item.name')
                                    ->label('Item Name')
                                    ->icon('heroicon-o-cube'),
                                
                                Infolists\Components\TextEntry::make('item.category.name')
                                    ->label('Item Category')
                                    ->icon('heroicon-o-tag'),
                                
                                Infolists\Components\TextEntry::make('item.unit_of_measure')
                                    ->label('Unit of Measure')
                                    ->icon('heroicon-o-scale'),
                            ]),
                        
                    ])
                    ->columns(1),
                
                Infolists\Components\Section::make('Stock Movement Details')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('current_stock')
                                    ->label('Current Stock')
                                    ->badge()
                                    ->color(fn ($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                                    ->icon('heroicon-o-archive-box'),
                                
                                Infolists\Components\TextEntry::make('max_limit')
                                    ->label('Max Limit')
                                    ->getStateUsing(function ($record) {
                                        $setting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                                            ->where('item_id', $record->item_id)
                                            ->first();
                                        return $setting ? $setting->max_limit : 'No limit set';
                                    })
                                    ->badge()
                                    ->color(fn ($state, $record) => 
                                        $state === 'No limit set' ? 'gray' : 
                                        ($record->current_stock > $state ? 'danger' : 
                                        ($record->current_stock == $state ? 'warning' : 'success'))
                                    )
                                    ->icon('heroicon-o-scale'),
                                
                                Infolists\Components\TextEntry::make('stock_status')
                                    ->label('Stock Status')
                                    ->getStateUsing(function ($record) {
                                        $setting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                                            ->where('item_id', $record->item_id)
                                            ->first();
                                        
                                        if (!$setting) {
                                            return 'No limit set';
                                        }
                                        
                                        if ($record->current_stock > $setting->max_limit) {
                                            return 'Over limit';
                                        } elseif ($record->current_stock == $setting->max_limit) {
                                            return 'At limit';
                                        } else {
                                            return 'Within limit';
                                        }
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Over limit' => 'danger',
                                        'At limit' => 'warning',
                                        'Within limit' => 'success',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-exclamation-triangle'),
                                
                                Infolists\Components\TextEntry::make('requests')
                                    ->label('Latest Stock Requests Quantity')
                                    ->getStateUsing(function ($record) {
                                        $latestRequest = $record->requests()
                                            ->whereHas('items', fn ($q) => $q->where('item_id', $record->item_id))
                                            ->latest()
                                            ->first();
                                            
                                        if ($latestRequest) {
                                            $requestItem = $latestRequest->items()
                                                ->where('item_id', $record->item_id)
                                                ->first();
                                            return $requestItem ? $requestItem->quantity : 0;
                                        }
                                        
                                        return 0;
                                    })
                                    ->icon('heroicon-o-arrow-up-tray'),
                                
                                Infolists\Components\TextEntry::make('requests')
                                    ->label('Latest Stock Requests Requester')
                                    ->getStateUsing(function ($record) {
                                        $latestRequest = $record->requests()
                                            ->whereHas('items', fn ($q) => $q->where('item_id', $record->item_id))
                                            ->latest()
                                            ->first();
                                            
                                        return $latestRequest ? $latestRequest->requester->name : '-';
                                    })
                                    ->icon('heroicon-o-user'),
                                
                                Infolists\Components\TextEntry::make('requests')
                                    ->label('Latest Stock Requests Date')
                                    ->getStateUsing(function ($record) {
                                        $latestRequest = $record->requests()
                                            ->whereHas('items', fn ($q) => $q->where('item_id', $record->item_id))
                                            ->latest()
                                            ->first();
                                            
                                        return $latestRequest ? $latestRequest->created_at->format('d M Y') : '-';
                                    })
                                    ->icon('heroicon-o-calendar'),
                                
                                Infolists\Components\TextEntry::make('usages')
                                    ->label('Latest Stock Usages Quantity')
                                    ->getStateUsing(function ($record) {
                                        $latestUsage = $record->usages()
                                            ->whereHas('items', fn ($q) => $q->where('item_id', $record->item_id))
                                            ->latest()
                                            ->first();
                                            
                                        if ($latestUsage) {
                                            $usageItem = $latestUsage->items()
                                                ->where('item_id', $record->item_id)
                                                ->first();
                                            return $usageItem ? $usageItem->quantity : 0;
                                        }
                                        
                                        return 0;
                                    })
                                    ->icon('heroicon-o-arrow-down-tray'),
                                
                                Infolists\Components\TextEntry::make('usages')
                                    ->label('Latest Stock Usages Date')
                                    ->getStateUsing(function ($record) {
                                        $latestUsage = $record->usages()
                                            ->whereHas('items', fn ($q) => $q->where('item_id', $record->item_id))
                                            ->latest()
                                            ->first();
                                            
                                        return $latestUsage ? $latestUsage->created_at->format('d M Y') : '-';
                                    })
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OfficeStationeryStockRequestsRelationManager::class,
            RelationManagers\StockUsagesRelationManager::class,
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']);
    }

    public static function canView($record): bool
    {
        return auth()->user()->hasRole(['Super Admin']) || 
            (auth()->user()->division_id === $record->division_id);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']) &&
            (auth()->user()->division_id === $record->division_id);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']) &&
            (auth()->user()->division_id === $record->division_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficeStationeryStocksPerDivision::route('/'),
            'view' => Pages\ViewOfficeStationeryStockPerDivision::route('/{record}'),
        ];
    }
}
