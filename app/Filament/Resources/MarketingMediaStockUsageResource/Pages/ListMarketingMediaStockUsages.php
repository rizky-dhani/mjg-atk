<?php

namespace App\Filament\Resources\MarketingMediaStockUsageResource\Pages;

use App\Filament\Resources\MarketingMediaStockUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaStockUsages extends ListRecords
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->division_id !== null),
        ];
    }
}