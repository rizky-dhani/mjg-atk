<?php

namespace App\Filament\Resources\OfficeStationeryInventorySettingResource\Pages;

use App\Filament\Resources\OfficeStationeryInventorySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeStationeryInventorySettings extends ListRecords
{
    protected static string $resource = OfficeStationeryInventorySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Settings'),
        ];
    }
}
