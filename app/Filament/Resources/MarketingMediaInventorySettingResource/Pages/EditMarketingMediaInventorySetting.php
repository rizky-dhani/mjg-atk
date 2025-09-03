<?php

namespace App\Filament\Resources\MarketingMediaInventorySettingResource\Pages;

use App\Filament\Resources\MarketingMediaInventorySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingMediaInventorySetting extends EditRecord
{
    protected static string $resource = MarketingMediaInventorySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}