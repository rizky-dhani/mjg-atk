<?php

namespace App\Filament\Resources\PrintMediaStockMovementResource\Pages;

use App\Filament\Resources\PrintMediaStockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintMediaStockMovements extends ListRecords
{
    protected static string $resource = PrintMediaStockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Stock Movement'),
        ];
    }
}
