<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrintMediaCategoryResource\Pages;
use App\Filament\Resources\PrintMediaCategoryResource\RelationManagers;
use App\Models\PrintMediaCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrintMediaCategoryResource extends Resource
{
    protected static ?string $model = PrintMediaCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $navigationParentItem = 'Print Media';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('print_media_items_count')
                    ->counts('printMediaItems')
                    ->label('Items Count'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->modal(),
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
            RelationManagers\PrintMediaItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrintMediaCategories::route('/'),
            'view' => Pages\ViewPrintMediaCategory::route('/{record}'),
        ];
    }
}
