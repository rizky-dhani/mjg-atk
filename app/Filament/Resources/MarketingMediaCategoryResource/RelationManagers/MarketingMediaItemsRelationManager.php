<?php

namespace App\Filament\Resources\MarketingMediaCategoryResource\RelationManagers;

use App\Filament\Resources\MarketingMediaResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'marketingMediaItems';

    protected static ?string $title = 'Marketing Media Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('paper, vinyl, canvas, etc.'),
                Forms\Components\TextInput::make('size')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('A4, A3, 36 inch, etc.'),
                Forms\Components\TextInput::make('unit_of_measure')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('sheet, roll, yard, meter, etc.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('quality')
                    ->colors([
                        'danger' => 'low',
                        'warning' => 'standard',
                        'success' => 'high',
                        'primary' => 'premium',
                    ]),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Cost/Unit')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_movements_count')
                    ->counts('stockMovements')
                    ->label('Movements'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn (int $state): string => number_format($state))
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('quality')
                    ->options([
                        'low' => 'Low',
                        'standard' => 'Standard',
                        'high' => 'High',
                        'premium' => 'Premium',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Item')
                    ->url(fn () => MarketingMediaResource::getUrl('create')),
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
            ]);
    }
}
