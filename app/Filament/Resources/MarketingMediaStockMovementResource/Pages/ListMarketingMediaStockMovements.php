<?php

namespace App\Filament\Resources\MarketingMediaStockMovementResource\Pages;

use App\Filament\Resources\MarketingMediaStockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaStockMovements extends ListRecords
{
    protected static string $resource = MarketingMediaStockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Stock Movement'),
        ];
    }
}
