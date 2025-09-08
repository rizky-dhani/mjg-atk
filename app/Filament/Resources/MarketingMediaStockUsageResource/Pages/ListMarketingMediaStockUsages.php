<?php

namespace App\Filament\Resources\MarketingMediaStockUsageResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\MarketingMediaStockUsageResource;

class ListMarketingMediaStockUsages extends ListRecords
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

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
                ->label('New Pengeluaran Media Cetak'),
        ];
    }
}
