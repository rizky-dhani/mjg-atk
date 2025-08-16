<?php

namespace App\Filament\Resources\MarketingMediaStockMovementResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use App\Models\MarketingMediaStockMovement;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\MarketingMediaStockMovementResource;

class ViewMarketingMediaStockMovement extends ViewRecord
{
    protected static string $resource = MarketingMediaStockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->modal(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Movement Information')
                    ->schema([
                        TextEntry::make('marketingMedia.name')
                            ->label('Marketing Media'),
                        TextEntry::make('division.name')
                            ->label('Division'),
                        TextEntry::make('movement_type')
                            ->label('Movement Type')
                            ->formatStateUsing(function (string $state): string {
                                $labels = MarketingMediaStockMovement::MOVEMENT_TYPES;
                                return $labels[$state] ?? ucfirst($state);
                            }),
                        TextEntry::make('movement_date')
                            ->label('Movement Date')
                            ->date(),
                    ])
                    ->columns(2),
                    
                \Filament\Infolists\Components\Section::make('Quantity & Reference')
                    ->schema([
                        TextEntry::make('quantity')
                            ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state))
                            ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                        TextEntry::make('previous_stock')
                            ->label('Previous Stock'),
                        TextEntry::make('reference_number')
                            ->label('Reference Number'),
                    ])
                    ->columns(3),
                    
                \Filament\Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('notes'),
                        TextEntry::make('creator.name')
                            ->label('Created By'),
                    ])
                    ->columns(1),
            ]);
    }
}
