<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\RelationManagers;

use Filament\Infolists;
use Filament\Infolists\Infolist;
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
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\Section::make('Stock Request Detail')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('request_number')
                                            ->label('Request Number'),
                                        Infolists\Components\TextEntry::make('requester.name')
                                            ->label('Requester Name'),
                                        Infolists\Components\TextEntry::make('division.name')
                                            ->label('Division Name'),
                                    ]),
                            ])
                            ->columns(1),

                        Infolists\Components\Section::make('Stock Request Status')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Stock Request Type')
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'increase' => 'Stock Increase',
                                                default => ucfirst($state),
                                            })
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'increase' => 'primary',
                                                default => 'secondary',
                                            }),
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Stock Request Status')
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'pending' => 'Pending',
                                                'approved_by_head' => 'Approved by Head',
                                                'rejected_by_head' => 'Rejected by Head',
                                                'approved_by_ipc' => 'Approved by IPC',
                                                'rejected_by_ipc' => 'Rejected by IPC',
                                                'delivered' => 'Delivered',
                                                'completed' => 'Completed',
                                                default => ucfirst(str_replace('_', ' ', $state)),
                                            })
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'pending' => 'warning',
                                                'approved_by_head', 'approved_by_ipc' => 'success',
                                                'rejected_by_head', 'rejected_by_ipc' => 'danger',
                                                'delivered', 'completed' => 'success',
                                                default => 'secondary',
                                            }),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Stock Request Notes')
                                            ->columnSpan(3),
                                        Infolists\Components\TextEntry::make('rejection_reason')
                                            ->label('Stock Request Rejection Reason')
                                            ->visible(fn ($record) => in_array($record->status, ['rejected_by_head', 'rejected_by_ipc']))
                                            ->columnSpan(3),
                                        Infolists\Components\TextEntry::make('divisionHead.name')
                                            ->label('Stock Request Head Approval Name')
                                            ->placeholder('-')
                                            ->formatStateUsing(fn ($state, $record) => $record->approval_head_id ? 'Approved' : '-')
                                            ->visible(fn ($record) => $record->approval_head_id || $record->status !== 'pending'),
                                        Infolists\Components\TextEntry::make('approval_head_at')
                                            ->label('Head Approval At')
                                            ->dateTime()
                                            ->placeholder('-')
                                            ->visible(fn ($record) => $record->approval_head_id || $record->status !== 'pending'),
                                        Infolists\Components\TextEntry::make('ipcStaff.name')
                                            ->label('IPC Approval Name')
                                            ->placeholder('-')
                                            ->formatStateUsing(fn ($state, $record) => $record->approval_ipc_id ? 'Approved' : '-')
                                            ->visible(fn ($record) => $record->approval_ipc_id || in_array($record->status, ['approved_by_ipc', 'delivered', 'completed'])),
                                        Infolists\Components\TextEntry::make('approval_ipc_at')
                                            ->label('IPC Approval At')
                                            ->dateTime()
                                            ->placeholder('-')
                                            ->visible(fn ($record) => $record->approval_ipc_id || in_array($record->status, ['approved_by_ipc', 'delivered', 'completed'])),
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
                                                    ->label('Item Name'),
                                                Infolists\Components\TextEntry::make('category.name')
                                                    ->label('Item Category Name'),
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
