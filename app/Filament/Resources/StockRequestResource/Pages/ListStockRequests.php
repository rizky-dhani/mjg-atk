<?php

namespace App\Filament\Resources\StockRequestResource\Pages;

use App\Filament\Resources\StockRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockRequests extends ListRecords
{
    protected static string $resource = StockRequestResource::class;

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
