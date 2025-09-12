<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;

use Filament\Tables;
use Filament\Infolists;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use App\Models\OfficeStationeryStockUsage;
use Filament\Resources\RelationManagers\RelationManager;

class OfficeStationeryStockUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';
    protected static ?string $title = 'Pengeluaran ATK';

    public function table(Table $table): Table
    {
        $ownerRecord = $this->getOwnerRecord();
        $itemId = $ownerRecord->item_id;

        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query
                ->where('division_id', $ownerRecord->division_id)
                ->whereHas('items', fn ($q) => $q->where('item_id', $itemId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Usage #')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Pengeluaran ATK')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\Section::make('Pengeluaran ATK Details')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label('Usage Number'),
                                        Infolists\Components\TextEntry::make('requester.name')
                                            ->label('Requester Name'),
                                        Infolists\Components\TextEntry::make('division.name')
                                            ->label('Division Name'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                                                OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
                                                default => ucfirst(str_replace('_', ' ', $state)),
                                            })
                                            ->color(fn ($state) => match ($state) {
                                                OfficeStationeryStockUsage::STATUS_PENDING => 'warning',
                                                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD, => 'success',
                                                OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD,OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD, => 'danger',OfficeStationeryStockUsage::STATUS_COMPLETED => 'success',
                                                default => 'secondary',
                                            }),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime(),
                                        Infolists\Components\TextEntry::make('head.name')
                                            ->label('Approved By')
                                            ->placeholder('-')
                                            ->formatStateUsing(fn ($state, $record) => $record->head_id ? 'Approved' : '-'),
                                    ]),
                            ])
                            ->columns(1),

                        Infolists\Components\Section::make('Pengeluaran ATK Items')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('items')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('item.name')
                                                    ->label('Item Name'),
                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->label('Qty'),
                                                Infolists\Components\TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->placeholder('-'),
                                            ]),
                                    ])
                                    ->columns(1),
                            ])
                            ->columns(1),
                    ]),
            ])
            ->emptyStateHeading('No Pengeluaran ATK record exists for this item');
    }
}
