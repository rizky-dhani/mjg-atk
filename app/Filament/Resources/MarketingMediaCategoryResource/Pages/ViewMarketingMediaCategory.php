<?php

namespace App\Filament\Resources\MarketingMediaCategoryResource\Pages;

use App\Filament\Resources\MarketingMediaCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaCategory extends ViewRecord
{
    protected static string $resource = MarketingMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
