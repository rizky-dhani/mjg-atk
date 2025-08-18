<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionOfficeStationeryStockRequestStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        
        // Get counts for all statuses
        $pendingCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
            
        $approvedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->count();
            
        $rejectedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD)
            ->count();
            
        $approvedByIpcCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC)
            ->count();
            
        $rejectedByIpcCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC)
            ->count();
            
        $approvedByIpcHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD)
            ->count();
            
        $rejectedByIpcHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD)
            ->count();
            
        $deliveredCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_DELIVERED)
            ->count();
            
        $approvedStockAdjustmentCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT)
            ->count();
            
        $approvedByGaAdminCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN)
            ->count();
            
        $rejectedByGaAdminCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN)
            ->count();
            
        $approvedByHcgHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD)
            ->count();
            
        $rejectedByHcgHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD)
            ->count();
            
        $completedCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        return [
            Stat::make('Pending', $pendingCount)
                ->description('Awaiting division head approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by Head', $approvedByHeadCount)
                ->description('Division head approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by Head', $rejectedByHeadCount)
                ->description('Division head rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by IPC', $approvedByIpcCount)
                ->description('IPC admin approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by IPC', $rejectedByIpcCount)
                ->description('IPC admin rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by IPC Head', $approvedByIpcHeadCount)
                ->description('IPC head approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by IPC Head', $rejectedByIpcHeadCount)
                ->description('IPC head rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Delivered', $deliveredCount)
                ->description('Marked as delivered')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_DELIVERED
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Stock Adj. Approved', $approvedStockAdjustmentCount)
                ->description('Stock adjustment approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by GA Admin', $approvedByGaAdminCount)
                ->description('GA admin approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by GA Admin', $rejectedByGaAdminCount)
                ->description('GA admin rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by HCG Head', $approvedByHcgHeadCount)
                ->description('HCG head approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by HCG Head', $rejectedByHcgHeadCount)
                ->description('HCG head rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Completed', $completedCount)
                ->description('Fully processed requests')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_COMPLETED
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