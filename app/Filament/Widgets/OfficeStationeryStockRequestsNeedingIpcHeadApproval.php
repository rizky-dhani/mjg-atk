<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryStockRequestsNeedingIpcHeadApproval extends BaseWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC Head approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC)
            ->where('type', OfficeStationeryStockRequest::TYPE_INCREASE)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'IPC');
            })
            ->count();

        return [
            Stat::make('Requests Needing IPC Head Approval', $requestsCount)
                ->description('As IPC Head')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Head') && $user->division->initial === 'IPC';
    }
}