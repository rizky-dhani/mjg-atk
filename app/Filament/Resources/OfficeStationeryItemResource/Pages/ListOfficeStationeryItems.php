<?php

namespace App\Filament\Resources\OfficeStationeryItemResource\Pages;

use App\Filament\Resources\OfficeStationeryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeStationeryItems extends ListRecords
{
    protected static string $resource = OfficeStationeryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Item'),
        ];
    }
}
