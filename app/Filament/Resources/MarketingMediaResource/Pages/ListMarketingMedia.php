<?php

namespace App\Filament\Resources\MarketingMediaResource\Pages;

use App\Filament\Resources\MarketingMediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMedia extends ListRecords
{
    protected static string $resource = MarketingMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Marketing Media'),
        ];
    }
}
