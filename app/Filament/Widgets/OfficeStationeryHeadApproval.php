<?php

namespace App\Filament\Widgets;

use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Alat Tulis Kantor';
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need Head approval for the user's division
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->where('division_id', $user->division_id)
            ->count();
            
        // Get usages that need Head approval for the user's division
        $usagesCount = OfficeStationeryStockUsage::where('status', OfficeStationeryStockUsage::STATUS_PENDING)
            ->where('division_id', $user->division_id)
            ->count();

        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_PENDING
                    ])
                )
                ->description('Pemasukan Barang')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
                
            Stat::make('Waiting for Approval', $usagesCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockUsage::STATUS_PENDING
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
        // Only show to actual Division Heads, not IPC or GA Heads
        return $user && $user->hasRole('Head') && $user->division_id && 
            !in_array($user->division->initial ?? '', ['IPC', 'GA', 'HCG']);
    }
}