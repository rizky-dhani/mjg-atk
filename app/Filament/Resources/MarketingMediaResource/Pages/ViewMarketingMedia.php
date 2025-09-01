<?php

namespace App\Filament\Resources\MarketingMediaResource\Pages;

use App\Filament\Resources\MarketingMediaResource;
use App\Models\MarketingMediaStockRequest;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class ViewMarketingMedia extends ViewRecord
{
    protected static string $resource = MarketingMediaResource::class;

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
                \Filament\Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('category.name')
                            ->label('Category'),
                        TextEntry::make('division.name')
                            ->label('Division'),
                        TextEntry::make('size'),
                        TextEntry::make('current_stock')
                            ->label('Current')
                            ->formatStateUsing(fn (int $state): string => number_format($state))
                            ->color('primary'),
                        TextEntry::make('latest_request.quantity')
                            ->label('Latest Stock Change')
                            ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ])
                    ->columns(3),
            ]);
    }
}
