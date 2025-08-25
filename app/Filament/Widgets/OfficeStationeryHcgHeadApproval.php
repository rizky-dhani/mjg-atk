<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OfficeStationeryHcgHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Office Stationery';
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        // Get requests that need GA Admin approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN)
            ->count();
            
        // Get usages that need GA Admin approval
        $usagesCount = OfficeStationeryStockUsage::where('status', OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'GA');
            })
            ->count();
        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN
                    ])
                )
                ->description('Stock Requests')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Waiting for Approval', $usagesCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN
                    ])
                )
                ->description('Stock Usages')
                ->color('warning')
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to GA Admins
        return $user && $user->hasRole('Admin') && $user->division_id && $user->division?->name == 'Human Capital General';
    }
}
