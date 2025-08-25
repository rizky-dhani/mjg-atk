<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryIpcHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Office Stationery';
    protected static bool $isLazy = false;
    protected static ?int $sort = 9;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC Head approval (IPC Admin approved)
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC)
            ->where('type', OfficeStationeryStockRequest::TYPE_INCREASE)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'IPC');
            })
            ->count();
        
        
        // Get requests that need second IPC Head approval (after stock adjustment)
        $secondRequestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT)
            ->where('type', OfficeStationeryStockRequest::TYPE_INCREASE)
            ->whereHas('division', function ($query) {
                $query->where('initial', 'IPC');
            })
            ->count();

        return [
            Stat::make('Stock Requests before Stock Adjustment', $requestsCount)
                ->description('IPC Admin Approved Requests')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC
                    ])
                )
                ->icon('heroicon-o-document-text'),
            Stat::make('Stock Requests After Stock Adjustment', $secondRequestsCount)
                ->description('Stock Adjustment Approved Requests')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('purple')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to IPC Heads
        return $user && $user->hasRole('Head') && $user->division && $user->division->initial === 'IPC';
    }
}