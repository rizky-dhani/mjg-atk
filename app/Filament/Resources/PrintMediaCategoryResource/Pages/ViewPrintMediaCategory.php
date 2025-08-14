<?php

namespace App\Filament\Resources\PrintMediaCategoryResource\Pages;

use App\Filament\Resources\PrintMediaCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPrintMediaCategory extends ViewRecord
{
    protected static string $resource = PrintMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
