<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\MarketingMedia;
use Filament\Tables\Table;
use App\Models\CompanyDivision;
use Filament\Resources\Resource;
use App\Models\MarketingMediaCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MarketingMediaResource\Pages;
use App\Filament\Resources\MarketingMediaResource\RelationManagers;

class MarketingMediaResource extends Resource
{
    protected static ?string $model = MarketingMedia::class;
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Marketing Media';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(MarketingMediaCategory::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('division_id')
                            ->label('Division')
                            ->options(CompanyDivision::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('size')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('A4, A3, 36 inch, etc.'),
                    ])
                    ->columns(2),
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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn (int $state): string => number_format($state))
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(MarketingMediaCategory::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(CompanyDivision::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
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
            RelationManagers\StockRequestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMedia::route('/'),
            'view' => Pages\ViewMarketingMedia::route('/{record}'),
        ];
    }
}
