<?php

namespace App\Filament\Resources\MarketingMediaStockPerDivisionResource\Pages;

use App\Filament\Resources\MarketingMediaStockPerDivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaStockPerDivision extends ViewRecord
{
    protected static string $resource = MarketingMediaStockPerDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}