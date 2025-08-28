<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OfficeStationeryStockUsageResource;

class ListOfficeStationeryStockUsages extends ListRecords
{
    protected static string $resource = OfficeStationeryStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function(array $data){
                    $data['requested_by'] = auth()->user()->id;
                    $data['division_id'] = auth()->user()->division_id;
                    return $data;
                })
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->label('New Stock Usage'),
        ];
    }
}
