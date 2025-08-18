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
        
        // Get counts for different statuses
        $pendingCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
            
        $approvedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->count();
            
        $rejectedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD)
            ->count();
            
        $deliveredCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_DELIVERED)
            ->count();
            
        $completedCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        return [
            Stat::make('Pending Requests', $pendingCount)
                ->description('Requests awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Approved by Head', $approvedByHeadCount)
                ->description('Requests approved by division head')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Rejected by Head', $rejectedByHeadCount)
                ->description('Requests rejected by division head')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Delivered Requests', $deliveredCount)
                ->description('Requests marked as delivered')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_DELIVERED
                    ])
                )
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Completed Requests', $completedCount)
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