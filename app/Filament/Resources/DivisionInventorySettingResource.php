<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\item;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CompanyDivision;
use Filament\Resources\Resource;
use App\Models\OfficeStationeryItem;
use App\Models\DivisionInventorySetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DivisionInventorySettingResource\Pages;
use App\Filament\Resources\DivisionInventorySettingResource\RelationManagers;

class DivisionInventorySettingResource extends Resource
{
    protected static ?string $model = DivisionInventorySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Inventory Limit';
    protected static ?string $modelLabel = 'Inventory Limit';
    protected static ?string $ppluralModelLabel = 'Inventory Limits';
    protected static ?int $navigationSort = 1;
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inventory Setting')
                    ->schema([
                        Forms\Components\Select::make('division_id')
                            ->label('Division')
                            ->relationship('division', 'name')
                            ->required()
                            ->default(auth()->user()->division_id),
                        Forms\Components\Select::make('item_id')
                            ->label('Office Stationery Item')
                            ->relationship('item', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Hidden::make('category_id')
                            ->default(function (Get $get) {
                                $itemId = $get('item_id');
                                if ($itemId) {
                                    $item = OfficeStationeryItem::find($itemId);
                                    return $item?->category->id;
                                }
                                return null;
                            }),
                        Forms\Components\TextInput::make('max_limit')
                            ->label('Maximum Limit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Maximum stock limit for this item in this division'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('division_id', auth()->user()->division_id))
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_limit')
                    ->label('Max Limit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->getStateUsing(function ($record) {
                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        return $stock ? $stock->current_stock : 0;
                    })
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        
                        if (!$stock) {
                            return 'No stock record';
                        }
                        
                        if ($stock->current_stock > $record->max_limit) {
                            return 'Over limit';
                        } elseif ($stock->current_stock == $record->max_limit) {
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('item_id')
                    ->label('Item')
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
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter based on user role
        if (auth()->user()->hasRole('Division Admin') || auth()->user()->hasRole('Division Head')) {
            $query->where('division_id', auth()->user()->division_id);
        }
        
        return $query;
    }
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Head', 'Admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin', 'Admin']);
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
            'index' => Pages\ListDivisionInventorySettings::route('/'),
        ];
    }
}
