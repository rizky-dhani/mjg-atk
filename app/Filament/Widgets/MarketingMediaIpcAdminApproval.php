<?php

namespace App\Filament\Widgets;

use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MarketingMediaIpcAdminApproval extends BaseWidget
{
    protected ?string $heading = 'Media Cetak';
    protected static bool $isLazy = false;
    protected static ?int $sort = 3;
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC Admin approval
        $stockAdjustmentCount = MarketingMediaStockRequest::where('status', MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_HEAD)
            ->count();

        return [
            Stat::make('Pemasukan Barang', $stockAdjustmentCount)
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][values][0]' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD
                    ])
                )
                ->description('Stock Adjustments')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to GA Admins
        return $user && $user->hasRole('Admin') && $user->division_id && $user->division->initial === 'IPC';
    }
}