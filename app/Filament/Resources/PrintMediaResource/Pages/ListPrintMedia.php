<?php

namespace App\Filament\Resources\PrintMediaResource\Pages;

use App\Filament\Resources\PrintMediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintMedia extends ListRecords
{
    protected static string $resource = PrintMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Print Media'),
        ];
    }
}
