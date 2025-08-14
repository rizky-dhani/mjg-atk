<?php

namespace App\Filament\Resources\CompanyDivisionResource\Pages;

use App\Filament\Resources\CompanyDivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyDivisions extends ListRecords
{
    protected static string $resource = CompanyDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->label('New Division'),
        ];
    }
}
