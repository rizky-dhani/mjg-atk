<?php

namespace App\Filament\Resources\MarketingMediaResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\MarketingMediaStockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';
    protected static ?string $title = 'Stock Movements';
    public function isReadOnly(): bool
    {
        return false;
    }
    protected static bool $canCreateAnother = true;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(), // Disabled because it should match the marketing media's division
                Forms\Components\Select::make('movement_type')
                    ->label('Type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Adjustment',
                        'damaged' => 'Damaged',
                        'expired' => 'Expired',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->step(1),
                Forms\Components\TextInput::make('previous_stock')
                    ->label('Previous Stock')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->default(0),
                Forms\Components\DatePicker::make('movement_date')
                    ->label('Date')
                    ->required()
                    ->default(now()),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(1000)
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
            // ->where('division_id', auth()->user()->division_id)
            ->orderByDesc('created_at')->orderByDesc('movement_date'))
            ->columns([
                Tables\Columns\BadgeColumn::make('movement_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                        'warning' => ['transfer', 'adjustment'],
                        'gray' => ['damaged', 'expired'],
                    ])
                    ->formatStateUsing(function (string $state): string {
                        $labels = MarketingMediaStockMovement::MOVEMENT_TYPES;
                        return $labels[$state] ?? ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('previous_stock')
                    ->label('Previous Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state)),
                Tables\Columns\TextColumn::make('new_stock')
                    ->label('New Stock')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record->previous_stock + $record->quantity)
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Adjustment',
                        'damaged' => 'Damaged',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Stock Movement')
                    ->visible(fn () => true)
                    ->hidden(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}