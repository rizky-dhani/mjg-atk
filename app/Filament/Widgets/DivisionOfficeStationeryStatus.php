<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionOfficeStationeryStatus extends BaseWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Office Stationery (My Division)';
    protected function getColumns(): int
    {
        return 4;
    }
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        $stockRequestUrl = route('filament.dashboard.resources.office-stationery-stock-requests.index');
        $stockUsageUrl = route('filament.dashboard.resources.office-stationery-stock-usages.index');

        $approvedStockRequestFilters = [
            'tableFilters' => [
                'division_id' => [
                    'value' => $user->division_id,
                ],
                'status' => [
                    'values' => [
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC,
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                        OfficeStationeryStockRequest::STATUS_DELIVERED,
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD
                    ],
                ],
            ],
        ];

        $rejectedStockRequestFilters = [
            'tableFilters' => [
                'division_id' => [
                    'value' => $user->division_id,
                ],
                'status' => [
                    'values' => [
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                    ],
                ],
            ],
        ];

        $approvedStockUsageFilters = [
            'tableFilters' => [
                'division_id' => [
                    'value' => $user->division_id,
                ],
                'status' => [
                    'values' => [
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD
                    ],
                ],
            ],
        ];

        $rejectedStockUsageFilters = [
            'tableFilters' => [
                'division_id' => [
                    'value' => $user->division_id,
                ],
                'status' => [
                    'values' => [
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, 
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, 
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD
                    ],
                ],
            ],
        ];

        $inProgressStockRequestUrl = $stockRequestUrl . '?' . http_build_query($approvedStockRequestFilters);
        $rejectedStockRequestUrl = $stockRequestUrl . '?' . http_build_query($rejectedStockRequestFilters);
        $inProgressStockUsageUrl = $stockUsageUrl . '?' . http_build_query($approvedStockUsageFilters);
        $rejectedStockUsageUrl = $stockUsageUrl . '?' . http_build_query($rejectedStockUsageFilters);

        // All Admin users see only their division's StockRequests
        // Get counts for all Stock Request statuses

        $pendingStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
        $inProgressStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->whereNotIn('status', [OfficeStationeryStockRequest::STATUS_PENDING, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_COMPLETED])
            ->count();
        $rejectedStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->whereIn('status', [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD])
            ->count();
        $completedStockRequestCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        // Get counts for all Stock Usage statuses

        $pendingStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockUsage::STATUS_PENDING)
            ->count();
        $inProgressStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->whereNotIn('status', [OfficeStationeryStockUsage::STATUS_PENDING, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD, OfficeStationeryStockUsage::STATUS_COMPLETED])
            ->count();
        $rejectedStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->whereIn('status', [OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD])
            ->count();
        $completedStockUsageCount = OfficeStationeryStockUsage::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockUsage::STATUS_COMPLETED)
            ->count();

        return [
            // Stock Requests
            Stat::make('Stock Request', $pendingStockRequestCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_PENDING,
                        'tableFilters[division_id][value]' => $user->division_id
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Request', $inProgressStockRequestCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url($inProgressStockRequestUrl)
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $rejectedStockRequestCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url($rejectedStockRequestUrl  )
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Request', $completedStockRequestCount)
                ->description('Fully processed requests')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_COMPLETED,
                        'tableFilters[division_id][value]' => $user->division_id
                    ])
                )
                ->icon('heroicon-o-document-text'),

            // Stock Usages
            Stat::make('Stock Usage', $pendingStockUsageCount)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockUsage::STATUS_PENDING,
                        'tableFilters[division_id][value]' => $user->division_id
                    ])
                )
                ->icon('heroicon-o-document-text'),

            Stat::make('Stock Usage', $inProgressStockUsageCount)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url($inProgressStockUsageUrl)
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $rejectedStockUsageCount)
                ->description('Rejected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url($rejectedStockUsageUrl)
                ->icon('heroicon-o-document-text'),
            
            Stat::make('Stock Usage', $completedStockUsageCount)
                ->description('Fully processed usages')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-usages.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockUsage::STATUS_COMPLETED,
                        'tableFilters[division_id][value]' => $user->division_id
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole(['Admin', 'Head']) && $user->division_id;
    }
}