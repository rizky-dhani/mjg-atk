<?php

namespace App\Filament\Widgets;

use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MarketingMediaHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Marketing Media';
    protected static bool $isLazy = false;
    protected static ?int $sort = 3;
    protected function getStats(): array
    {
        $user = Auth::user();
            
        // Get Marketing Media that need Head approval for the user's division
        $requestsCount = MarketingMediaStockRequest::where('status', MarketingMediaStockRequest::STATUS_PENDING)
        ->where('division_id', $user->division_id)
        ->count();
        
        // // Get usages that need Head approval for the user's division
        // $usagesCount = MarketingMediaStockUsage::where('status', MarketingMediaStockUsage::STATUS_PENDING)
        //     ->where('division_id', $user->division_id)
        //     ->count();

        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => MarketingMediaStockRequest::STATUS_PENDING
                    ])
                )
                ->description('Stock Requests')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
                
            // Stat::make('Stock Usages waiting for Approval', $usagesCount)
            //     ->description('Office Stationery Stock Usages')
            //     ->descriptionIcon('heroicon-m-user-group')
            //     ->color('warning')
            //     ->url(
            //         route('filament.dashboard.resources.office-stationery-stock-usages.index', [
            //             'tableFilters[status][value]' => OfficeStationeryStockUsage::STATUS_PENDING
            //         ])
            //     )
            //     ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to actual Division Heads, not IPC or GA Heads
        return $user && $user->hasRole('Head') && $user->division_id && 
            !in_array($user->division->initial ?? '', ['IPC', 'GA', 'HCG']);
    }
}