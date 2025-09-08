<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;

use Filament\Tables;
use Filament\Infolists;
use Filament\Tables\Table;
use App\Models\OfficeStationeryStockRequest;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

class OfficeStationeryStockRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';
    protected static ?string $title = 'Pemasukan ATK';
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
                Tables\Columns\TextColumn::make('requester.name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Pemasukan ATK')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                Infolists\Components\Section::make('Pemasukan ATK Detail')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('request_number')
                                    ->label('Request Number'),
                                Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1),
                Infolists\Components\Section::make('Pemasukan ATK Status')
                    ->schema([
                        Infolists\Components\Grid::make(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'success',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'danger',
                                        OfficeStationeryStockRequest::STATUS_DELIVERED, OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Head Approve')
                                    ->placeholder('-')
                                    ->formatStateUsing(fn ($record) => $record->approval_head_id ? 'Approved' : '-'),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('ipcAdmin.name')
                                    ->label('IPC Approve')
                                    ->formatStateUsing(fn ($record) => $record->approval_ipc_id ? 'Approved' : '-')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC]))
                                    ->columnSpan(5),
                            ]),
                    ])
                    ->columns(1),
                Infolists\Components\Section::make('Pemasukan ATK Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(5)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Name'),
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Qty'),
                                        Infolists\Components\TextEntry::make('adjusted_quantity')
                                            ->label('Adj. Qty'),
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
            ->emptyStateHeading('No Pemasukan ATK record exists for this item');
    }
}
