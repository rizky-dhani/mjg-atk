<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryItemPriceResource\Pages;
use App\Filament\Resources\OfficeStationeryItemPriceResource\RelationManagers;
use App\Models\OfficeStationeryItemPrice;
use App\Models\OfficeStationeryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfficeStationeryItemPriceResource extends Resource
{
    protected static ?string $model = OfficeStationeryItemPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';
    protected static ?string $navigationLabel = 'Item Prices';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->options(OfficeStationeryItem::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\DatePicker::make('effective_date')
                    ->label('Effective Date')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->prefix('Rp ')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Effective Date')
                    ->date(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->getStateUsing(function (OfficeStationeryItemPrice $record) {
                        return $record->isActive();
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Active')
                    ->query(fn (Builder $query) => $query->where(function ($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>', now());
                    })),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListOfficeStationeryItemPrices::route('/'),
        ];
    }
}