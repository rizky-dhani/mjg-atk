<?php

namespace App\Filament\Resources\PrintMediaCategoryResource\Pages;

use App\Filament\Resources\PrintMediaCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintMediaCategories extends ListRecords
{
    protected static string $resource = PrintMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Category'),
        ];
    }
}
