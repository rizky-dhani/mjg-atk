<?php

namespace App\Filament\Resources\MarketingMediaInventorySettingResource\Pages;

use App\Filament\Resources\MarketingMediaInventorySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaInventorySettings extends ListRecords
{
    protected static string $resource = MarketingMediaInventorySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Setting')
                ->icon('heroicon-o-plus')
                ->modal(),
        ];
    }
}