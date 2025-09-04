<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryItemResource\Pages;
use App\Filament\Resources\OfficeStationeryItemResource\RelationManagers;
use App\Models\OfficeStationeryItem;
use App\Models\OfficeStationeryCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfficeStationeryItemResource extends Resource
{
    protected static ?string $model = OfficeStationeryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Items';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationParentItem = 'Alat Tulis Kantor';
    
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\Select::make('office_stationery_category_id')
                            ->label('Category')
                            ->relationship('category', 'name', 
                                function (Builder $query) {
                                    return $query->orderBy('id');
                                })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('unit_of_measure')
                            ->label('Unit of Measure')
                            ->required()
                            ->maxLength(10)
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office_stationery_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
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
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Head', 'Admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
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
            'index' => Pages\ListOfficeStationeryItems::route('/'),
        ];
    }
}
