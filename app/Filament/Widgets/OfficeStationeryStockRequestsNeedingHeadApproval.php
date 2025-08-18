<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryStockRequestsNeedingHeadApproval extends BaseWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need Head approval for the user's division
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->where('division_id', $user->division_id)
            ->count();

        return [
            Stat::make('Requests Needing Your Approval', $requestsCount)
                ->description('As Division Head')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to actual Division Heads, not IPC or GA Heads
        return $user && $user->hasRole('Head') && $user->division_id && 
               !in_array($user->division->initial ?? '', ['IPC', 'GA']);
    }
}