<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use App\Filament\Resources\MarketingMediaStockRequestResource;
use App\Models\MarketingMediaStockRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListMarketingMediaStockRequests extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Stock Request'),
        ];
    }
}
