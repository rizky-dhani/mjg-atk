<?php

namespace App\Filament\Resources\DivisionInventorySettingResource\Pages;

use App\Filament\Resources\DivisionInventorySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisionInventorySettings extends ListRecords
{
    protected static string $resource = DivisionInventorySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Settings'),
        ];
    }
}
