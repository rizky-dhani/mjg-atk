<?php

namespace App\Filament\Resources\MarketingMediaInventorySettingResource\Pages;

use App\Filament\Resources\MarketingMediaInventorySettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingMediaInventorySetting extends CreateRecord
{
    protected static string $resource = MarketingMediaInventorySettingResource::class;

    protected static bool $canCreateAnother = false;
}