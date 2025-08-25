<?php

namespace App\Filament\Widgets;

use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockUsage;
use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionMarketingMediaStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Marketing Media';
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
        $pendingStockRequestCount = MarketingMediaStockRequest::where('division_id', $divisionId)
            ->where('status', MarketingMediaStockRequest::STATUS_PENDING)
            ->count();
        $inProgressStockRequestCount = MarketingMediaStockRequest::where('division_id', $divisionId)
            ->whereNotIn('status', [MarketingMediaStockRequest::STATUS_PENDING, MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD, MarketingMediaStockRequest::STATUS_COMPLETED])
            ->count();
        $rejectedStockRequestCount = MarketingMediaStockRequest::where('division_id', $divisionId)
            ->whereIn('status', [MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD])
            ->count();
        $completedStockRequestCount = MarketingMediaStockRequest::where('division_id', $divisionId)
            ->where('status', MarketingMediaStockRequest::STATUS_COMPLETED)
            ->count();

        // Get counts for all Stock Usage statuses
        $pendingStockUsageCount = MarketingMediaStockUsage::where('division_id', $divisionId)
            ->where('status', MarketingMediaStockUsage::STATUS_PENDING)
            ->count();
        $inProgressStockUsageCount = MarketingMediaStockUsage::where('division_id', $divisionId)
            ->whereNotIn('status', [MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD])
            ->count();
        $rejectedStockUsageCount = MarketingMediaStockUsage::where('division_id', $divisionId)
            ->whereIn('status', [MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD])
            ->count();
        $completedStockUsageCount = MarketingMediaStockUsage::where('division_id', $divisionId)
            ->where('status', MarketingMediaStockUsage::STATUS_COMPLETED)
            ->count();

        return [
            // Stock Requests
            Stat::make('Stock Request', $pendingStockRequestCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][value]' => MarketingMediaStockRequest::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Request', $inProgressStockRequestCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $rejectedStockRequestCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $completedStockRequestCount)
                ->description('Fully processed requests')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-requests.index', [
                        'tableFilters[status][value]' => MarketingMediaStockRequest::STATUS_COMPLETED
                    ])
                )
                ->icon('heroicon-o-document-text'),

            // Stock Usages
            Stat::make('Stock Usage', $pendingStockUsageCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-usages.index', [
                        'tableFilters[status][value]' => MarketingMediaStockUsage::STATUS_PENDING
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Usage', $inProgressStockUsageCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-usages.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $rejectedStockUsageCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-usages.index')
                )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $completedStockUsageCount)
                ->description('Fully processed usages')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.marketing-media-stock-usages.index', [
                        'tableFilters[status][value]' => MarketingMediaStockUsage::STATUS_COMPLETED
                    ])
                )
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
        
        return $user->division && in_array($user->division->name, $marketingDivisions);
    }
}