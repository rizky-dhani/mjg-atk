<?php

namespace App\Filament\Resources\MarketingMediaItemResource\Pages;

use App\Filament\Resources\MarketingMediaItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaItems extends ListRecords
{
    protected static string $resource = MarketingMediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Item'),
        ];
    }
}
