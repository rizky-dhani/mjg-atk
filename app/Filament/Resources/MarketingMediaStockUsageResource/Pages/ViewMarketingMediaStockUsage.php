<?php

namespace App\Filament\Resources\MarketingMediaStockUsageResource\Pages;

use App\Filament\Resources\MarketingMediaStockUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaStockUsage extends ViewRecord
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === \App\Models\MarketingMediaStockUsage::STATUS_PENDING && auth()->user()->id === $record->requested_by),
        ];
    }
}