<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionOfficeStationeryStockRequestStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Office Stationery Stock Request';
    protected function getColumns(): int
    {
        return 3;
    }
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        
        // Get counts for all statuses
        $pendingCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
        $inProgressCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', '!=' , [OfficeStationeryStockRequest::STATUS_PENDING, OfficeStationeryStockRequest::STATUS_COMPLETED])
            ->count();
        $completedCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        return [
            Stat::make('Pending', $pendingCount)
                ->description('Pending approval from head of division')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('In Progress', $inProgressCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index')
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