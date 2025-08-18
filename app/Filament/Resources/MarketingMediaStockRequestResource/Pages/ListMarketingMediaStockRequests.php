<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use App\Filament\Resources\MarketingMediaStockRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaStockRequests extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Stock Request'),
        ];
    }
}
