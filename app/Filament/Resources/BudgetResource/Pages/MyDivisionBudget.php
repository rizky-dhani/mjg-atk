<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use App\Filament\Resources\BudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class MyDivisionBudget extends ListRecords
{
    protected static string $resource = BudgetResource::class;
}
