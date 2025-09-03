<?php

namespace App\Filament\Resources\MarketingMediaStockPerDivisionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('usage_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('usage_number')
            ->columns([
                Tables\Columns\TextColumn::make('usage_number'),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved_by_head', 'approved_by_ga_admin', 'approved_by_marketing_support_head', 'completed' => 'success',
                        'rejected_by_head', 'rejected_by_ga_admin', 'rejected_by_marketing_support_head' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'approved_by_head' => 'Approved by Head',
                        'rejected_by_head' => 'Rejected by Head',
                        'approved_by_ga_admin' => 'Approved by GA Admin',
                        'rejected_by_ga_admin' => 'Rejected by GA Admin',
                        'approved_by_marketing_support_head' => 'Approved by Marketing Support Head',
                        'rejected_by_marketing_support_head' => 'Rejected by Marketing Support Head',
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