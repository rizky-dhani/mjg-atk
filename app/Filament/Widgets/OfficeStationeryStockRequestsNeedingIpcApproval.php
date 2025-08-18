<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryStockRequestsNeedingIpcApproval extends BaseWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->where('type', OfficeStationeryStockRequest::TYPE_INCREASE)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'IPC');
            })
            ->count();

        return [
            Stat::make('Requests Needing IPC Approval', $requestsCount)
                ->description('As IPC Admin')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }
}