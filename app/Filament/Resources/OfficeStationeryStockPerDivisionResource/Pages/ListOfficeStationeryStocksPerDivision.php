<?php

namespace App\Filament\Resources\OfficeStationeryStockPerDivisionResource\Pages;

use App\Filament\Resources\OfficeStationeryStockPerDivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListOfficeStationeryStocksPerDivision extends ListRecords
{
    protected static string $resource = OfficeStationeryStockPerDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah')
                ->modalWidth(MaxWidth::SevenExtraLarge),
        ];
    }
}
