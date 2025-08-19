<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use App\Filament\Resources\OfficeStationeryStockUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
                ->modalWidth('7xl'),
        ];
    }
}
