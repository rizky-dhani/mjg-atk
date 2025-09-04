<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use App\Models\MarketingMediaStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class MarketingMediaHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Media Cetak';
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        $user = Auth::user();
            
        // Get Marketing Media that need Head approval for the user's division
        $requestsCount = MarketingMediaStockRequest::where('status', MarketingMediaStockRequest::STATUS_PENDING)
        ->where('division_id', $user->division_id)
        ->count();
        
        // Get usages that need Head approval for the user's division
        $usagesCount = MarketingMediaStockUsage::where('status', MarketingMediaStockUsage::STATUS_PENDING)
            ->where('division_id', $user->division_id)
            ->count();

        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][value]' => MarketingMediaStockRequest::STATUS_PENDING
                    ])
                )
                ->description('Pemasukan Barang')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Waiting for Approval', $usagesCount)
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-usages.index', [
                        'tableFilters[status][value]' => MarketingMediaStockUsage::STATUS_PENDING
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
        
        // Check if user has a division
        if (!$user || !$user->division_id) {
            return false;
        }
        
        // Check if user's division is a Marketing division
        $marketingDivisions = [
            'Marketing Blood Bank',
            'Marketing Hospital',
            'Marketing Primary Care',
            'Marketing Primary Health',
            'Marketing Support'
        ];
        
        return $user->division && $user->hasRole('Head') && in_array($user->division->name, $marketingDivisions);
    }
}