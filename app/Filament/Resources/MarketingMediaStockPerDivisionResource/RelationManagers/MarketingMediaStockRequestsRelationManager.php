<?php

namespace App\Filament\Resources\MarketingMediaStockPerDivisionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaStockRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('request_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('request_number')
            ->columns([
                Tables\Columns\TextColumn::make('request_number'),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved_by_head', 'approved_by_ipc', 'approved_by_ipc_head', 'delivered', 'approved_stock_adjustment', 'approved_by_ga_admin', 'approved_by_mkt_head', 'completed' => 'success',
                        'rejected_by_head', 'rejected_by_ipc', 'rejected_by_ipc_head', 'rejected_by_ga_admin', 'rejected_by_mkt_head' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'approved_by_head' => 'Approved by Head',
                        'rejected_by_head' => 'Rejected by Head',
                        'approved_by_ipc' => 'Approved by IPC',
                        'rejected_by_ipc' => 'Rejected by IPC',
                        'approved_by_ipc_head' => 'Approved by IPC Head',
                        'rejected_by_ipc_head' => 'Rejected by IPC Head',
                        'delivered' => 'Delivered',
                        'approved_stock_adjustment' => 'Stock Adjustment Approved',
                        'rejected_by_ga_admin' => 'Rejected by GA Admin',
                        'approved_by_ga_admin' => 'Approved by GA Admin',
                        'rejected_by_mkt_head' => 'Rejected by Marketing Support Head',
                        'approved_by_mkt_head' => 'Approved by Marketing Support Head',
                        'completed' => 'Completed',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}