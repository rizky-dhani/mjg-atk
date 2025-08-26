<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryGaAdminApproval extends BaseWidget
{
    protected ?string $heading = 'Office Stationery';
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need GA Admin approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD)
            ->count();
            
        // Get usages that need GA Admin approval
        $usagesCount = OfficeStationeryStockUsage::where('status', OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD)
            ->count();

        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD
                    ])
                )
                ->description('Stock Requests')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Waiting for Approval', $usagesCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD
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
        return $user && $user->hasRole('Admin') && $user->division_id && $user->division?->name == 'General Affairs';
    }
}