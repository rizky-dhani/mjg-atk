<?php

namespace App\Filament\Resources\StockUsageResource\Pages;

use App\Filament\Resources\StockUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStockUsages extends ManageRecords
{
    protected static string $resource = StockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Stock Usage'),
        ];
    }
}
