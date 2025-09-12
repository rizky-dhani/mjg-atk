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
                                    ->badge()
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
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionHead.name')
                                    ->label('Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_head_at')
                                    ->label('Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('ipcAdmin.name')
                                    ->label('IPC Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpc.name')
                                    ->label('IPC Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_at')
                                    ->label('IPC Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('ipcHead.name')
                                    ->label('IPC Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_head_at')
                                    ->label('IPC Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpcHead.name')
                                    ->label('IPC Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_head_at')
                                    ->label('IPC Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approvalStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Approved By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('approval_stock_adjustment_at')
                                    ->label('Stock Adjustment Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejectionStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Rejected By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejection_stock_adjustment_at')
                                    ->label('Stock Adjustment Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('gaAdmin.name')
                                    ->label('GA Admin Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_admin_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                                    ->label('GA Admin Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                                    ->label('GA Admin Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('gaHead.name')
                                    ->label('GA Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_head_at')
                                    ->label('GA Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaHead.name')
                                    ->label('GA Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_head_at')
                                    ->label('GA Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('deliverer.name')
                                    ->label('Completed By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->delivered_by !== null && $record->status === OfficeStationeryStockRequest::STATUS_COMPLETED),
                                Infolists\Components\TextEntry::make('delivered_at')
                                    ->label('Completed At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->delivered_by !== null && $record->status === OfficeStationeryStockRequest::STATUS_COMPLETED),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD]))
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
