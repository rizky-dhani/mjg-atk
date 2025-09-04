<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class MarketingMediaIpcHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Media Cetak';
    protected static bool $isLazy = false;
    protected static ?int $sort = 9;
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC Head approval (IPC Admin approved)
        $requestsCount = MarketingMediaStockRequest::where('status', MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC)
            ->where('type', MarketingMediaStockRequest::TYPE_INCREASE)
            ->count();
        
        
        // Get requests that need second IPC Head approval (after stock adjustment)
        $secondRequestsCount = MarketingMediaStockRequest::where('status', MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT)
            ->where('type', MarketingMediaStockRequest::TYPE_INCREASE)
            ->count();

        return [
            Stat::make('Pemasukan Barang (Before Stock Adjustment)', $requestsCount)
                ->description('IPC Admin Approved Requests')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][values][0]' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
                    ])
                )
                ->icon('heroicon-o-document-text'),
            Stat::make('Pemasukan Barang (After Stock Adjustment)', $secondRequestsCount)
                ->description('Stock Adjustment Approved Requests')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('purple')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][values][0]' => MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT
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