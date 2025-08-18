<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryStockRequestsNeedingHcgHeadApproval extends BaseWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need HCG Head approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN)
            ->where('type', OfficeStationeryStockRequest::TYPE_REDUCTION)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'HCG');
            })
            ->count();

        return [
            Stat::make('Requests Needing HCG Head Approval', $requestsCount)
                ->description('As HCG Head')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Head') && $user->division && $user->division->initial === 'HCG';
    }
}