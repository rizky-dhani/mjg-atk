<?php

namespace App\Filament\Resources\MarketingMediaCategoryResource\Pages;

use App\Filament\Resources\MarketingMediaCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaCategories extends ListRecords
{
    protected static string $resource = MarketingMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Category'),
        ];
    }
}
