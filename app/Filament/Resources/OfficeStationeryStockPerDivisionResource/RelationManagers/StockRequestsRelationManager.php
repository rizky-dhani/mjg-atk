<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;

use Filament\Tables;
use Filament\Infolists;
use Filament\Tables\Table;
use App\Models\StockRequest;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

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
                Tables\Actions\ViewAction::make()
                    ->infolist([
                Infolists\Components\Section::make('Stock Request Detail')
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
                                        StockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        StockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1),
                Infolists\Components\Section::make('Stock Request Status')
                    ->schema([
                        Infolists\Components\Grid::make(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        StockRequest::STATUS_PENDING => 'Pending',
                                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                                        StockRequest::STATUS_DELIVERED => 'Delivered',
                                        StockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        StockRequest::STATUS_PENDING => 'warning',
                                        StockRequest::STATUS_APPROVED_BY_HEAD, StockRequest::STATUS_APPROVED_BY_IPC => 'success',
                                        StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC => 'danger',
                                        StockRequest::STATUS_DELIVERED, StockRequest::STATUS_COMPLETED => 'success',
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
                                Infolists\Components\TextEntry::make('ipcStaff.name')
                                    ->label('IPC Approve')
                                    ->formatStateUsing(fn ($record) => $record->approval_ipc_id ? 'Approved' : '-')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC]))
                                    ->columnSpan(5),
                            ]),
                    ])
                    ->columns(1),
                Infolists\Components\Section::make('Stock Request Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Name'),
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Quantity'),
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
            ->emptyStateHeading('No related requests');
    }
}
