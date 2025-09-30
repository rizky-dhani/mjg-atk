<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class DivisionBudgetStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Budget';
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        
        // Get ATK budget
        $atkBudget = Budget::where('division_id', $divisionId)
            ->where('type', 'ATK')
            ->first();
            
        // Get Marketing Media budget
        $marketingMediaBudget = Budget::where('division_id', $divisionId)
            ->where('type', 'Marketing Media')
            ->first();
            
        $stats = [];
        
        // ATK Budget Stats
        if ($atkBudget) {
            $initialAmount = $atkBudget->initial_amount;
            $currentAmount = $atkBudget->current_amount;
            $usedAmount = $initialAmount - $currentAmount;
            $usagePercentage = $initialAmount > 0 ? ($usedAmount / $initialAmount) * 100 : 0;
            
            $stats[] = Stat::make('ATK', 'Rp. '.number_format($initialAmount, 0, ',', '.'))
                ->description('Total budget')
                ->color('primary');
                
            $stats[] = Stat::make('ATK', 'Rp. '.number_format($usedAmount, 0, ',', '.'))
                ->description('Used')
                ->color('warning');
                
            $stats[] = Stat::make('ATK', 'Rp. '.number_format($currentAmount, 0, ',', '.'))
                ->description('Remaining')
                ->color('success');
                
            $stats[] = Stat::make('ATK', round($usagePercentage, 2) . '%')
                ->description('Budget utilization')
                ->color($usagePercentage > 80 ? 'danger' : ($usagePercentage > 50 ? 'warning' : 'success'));
        }
        
        // Marketing Media Budget Stats (only for divisions that have this budget)
        if ($marketingMediaBudget) {
            $initialAmount = $marketingMediaBudget->initial_amount;
            $currentAmount = $marketingMediaBudget->current_amount;
            $usedAmount = $initialAmount - $currentAmount;
            $usagePercentage = $initialAmount > 0 ? ($usedAmount / $initialAmount) * 100 : 0;
            
            $stats[] = Stat::make('Marketing Media', 'Rp. '.number_format($initialAmount, 0, ',', '.'))
                ->description('Total budget')
                ->color('primary');
                
            $stats[] = Stat::make('Marketing Media', 'Rp. '.number_format($usedAmount, 0, ',', '.'))
                ->description('Used')
                ->color('warning');
                
            $stats[] = Stat::make('Marketing Media', 'Rp. '.number_format($currentAmount, 0, ',', '.'))
                ->description('Remaining')
                ->color('success');
                
            $stats[] = Stat::make('Marketing Media', round($usagePercentage, 2) . '%')
                ->description('Budget utilization')
                ->color($usagePercentage > 80 ? 'danger' : ($usagePercentage > 50 ? 'warning' : 'success'));
        }
        
        return $stats;
    }
}
