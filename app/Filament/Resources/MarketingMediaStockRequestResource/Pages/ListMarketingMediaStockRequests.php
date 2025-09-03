<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use App\Filament\Resources\MarketingMediaStockRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListMarketingMediaStockRequests extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Penambahan Barang')
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = auth()->user()->division_id;
                    $data['requested_by'] = auth()->user()->id;
                    return $data;
                })
                ->modalWidth(MaxWidth::SevenExtraLarge),
        ];
    }
}
