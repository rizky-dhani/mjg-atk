<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaCategoryResource\Pages;
use App\Filament\Resources\MarketingMediaCategoryResource\RelationManagers;
use App\Models\MarketingMediaCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaCategoryResource extends Resource
{
    protected static ?string $model = MarketingMediaCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Marketing Media';
    protected static ?string $navigationLabel = 'Categories';
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
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items Count'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modal(),
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
        return auth()->user()->hasRole(['Super Admin', 'Admin', 'Head']);
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
            RelationManagers\MarketingMediaItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaCategories::route('/'),
        ];
    }
}