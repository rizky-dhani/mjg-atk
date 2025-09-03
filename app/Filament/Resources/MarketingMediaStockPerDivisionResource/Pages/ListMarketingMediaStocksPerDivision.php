<?php

namespace App\Filament\Resources\MarketingMediaStockPerDivisionResource\Pages;

use App\Filament\Resources\MarketingMediaStockPerDivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaStocksPerDivision extends ListRecords
{
    protected static string $resource = MarketingMediaStockPerDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}