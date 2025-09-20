<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OfficeStationeryHcgHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Alat Tulis Kantor';
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        // Get requests that need GA Admin approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN)
            ->count();
            
        // Get usages that need GA Admin approval
        $usagesCount = OfficeStationeryStockUsage::where('status', OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN)
            ->count();
        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN
                    ])
                )
                ->description('Pemasukan Barang')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Waiting for Approval', $usagesCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN
                    ])
                )
                ->description('Pengeluaran Barang')
                ->color('warning')
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to GA Admins
        return $user && $user->hasRole('Head') && $user->division_id && $user->division?->initial == 'HCG';
    }
}
