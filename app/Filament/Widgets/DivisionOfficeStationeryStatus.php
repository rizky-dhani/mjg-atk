<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionOfficeStationeryStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Office Stationery';
    protected function getColumns(): int
    {
        return 4;
    }
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        
        // Get counts for all Stock Request statuses
        $pendingStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
        $inProgressStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->whereNotIn('status', [OfficeStationeryStockRequest::STATUS_PENDING, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_COMPLETED])
            ->count();
        $rejectedStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->whereIn('status', [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD])
            ->count();
        $completedStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        // Get counts for all Stock Usage statuses
        $pendingStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockUsage::STATUS_PENDING)
            ->count();
        $inProgressStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->whereNotIn('status', [OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD])
            ->count();
        $rejectedStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->whereIn('status', [OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD])
            ->count();
        $completedStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockUsage::STATUS_COMPLETED)
            ->count();

        return [
            // Stock Requests
            Stat::make('Stock Request', $pendingStockRequestCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Request', $inProgressStockRequestCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $rejectedStockRequestCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $completedStockRequestCount)
                ->description('Fully processed requests')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_COMPLETED
                    ])
                )
                ->icon('heroicon-o-document-text'),

            // Stock Usages
            Stat::make('Stock Usage', $pendingStockUsageCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockUsage::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Usage', $inProgressStockUsageCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $rejectedStockUsageCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $completedStockUsageCount)
                ->description('Fully processed usages')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockUsage::STATUS_COMPLETED
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Admin') && $user->division_id;
    }
}