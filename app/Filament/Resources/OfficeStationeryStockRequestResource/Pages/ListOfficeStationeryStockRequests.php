<?php

namespace App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;

use App\Filament\Resources\OfficeStationeryStockRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeStationeryStockRequests extends ListRecords
{
    protected static string $resource = OfficeStationeryStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Stock Request')
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = auth()->user()->division_id;
                    $data['requested_by'] = auth()->user()->id;
                    return $data;
                }),
        ];
    }
}
