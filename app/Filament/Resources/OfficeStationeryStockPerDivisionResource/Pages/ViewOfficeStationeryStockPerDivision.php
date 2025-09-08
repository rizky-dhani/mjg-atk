<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\Pages;

use App\Filament\Resources\OfficeStationeryStockPerDivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOfficeStationeryStockPerDivision extends ViewRecord
{
    protected static string $resource = OfficeStationeryStockPerDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Tambah'),
        ];
    }
}
