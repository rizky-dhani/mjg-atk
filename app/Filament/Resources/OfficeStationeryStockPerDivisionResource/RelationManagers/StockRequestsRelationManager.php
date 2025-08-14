<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StockRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';
    protected static ?string $title = 'Stock Requests';

    public function table(Table $table): Table
    {
        $ownerRecord = $this->getOwnerRecord();
        $itemId = $ownerRecord->item_id;

        return $table
            ->recordTitleAttribute('request_number')
            ->modifyQueryUsing(fn ($query) => $query
                ->where('division_id', $ownerRecord->division_id)
                ->whereHas('items', fn ($q) => $q->where('item_id', $itemId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_number')->label('Request #')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Stock Request')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->emptyStateHeading('No related requests');
    }
}
