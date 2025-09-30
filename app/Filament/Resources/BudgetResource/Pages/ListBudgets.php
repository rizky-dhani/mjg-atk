<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\BudgetResource;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Budget')
                ->modal()
                ->modalHeading('Create New Budget')
                ->mutateFormDataUsing(function(array $data){
                    $data['current_amount'] = $data['initial_amount'];
                    return $data;
                }),
        ];
    }
}
