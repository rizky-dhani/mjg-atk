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
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('item.category.name')
                    ->label('Item Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->numeric()
                    ->sortable(),
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
                        Infolists\Components\Grid::make(5)
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

                                Infolists\Components\TextEntry::make('current_stock')
                                    ->label('Current Stock')
                                    ->badge()
                                    ->color(fn ($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                                    ->icon('heroicon-o-archive-box'),
                            ]),
                        
                    ])
                    ->columns(1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StockRequestsRelationManager::class,
            RelationManagers\StockUsagesRelationManager::class,
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Staff']);
    }

    public static function canView($record): bool
    {
        return auth()->user()->hasRole(['Super Admin']) || 
            (auth()->user()->division_id === $record->division_id);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Staff']) &&
            (auth()->user()->division_id === $record->division_id);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Staff']) &&
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
