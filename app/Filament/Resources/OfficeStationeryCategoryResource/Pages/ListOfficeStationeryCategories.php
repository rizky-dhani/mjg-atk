<?php

namespace App\Filament\Resources\OfficeStationeryCategoryResource\Pages;

use App\Filament\Resources\OfficeStationeryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeStationeryCategories extends ListRecords
{
    protected static string $resource = OfficeStationeryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah'),
        ];
    }
}
