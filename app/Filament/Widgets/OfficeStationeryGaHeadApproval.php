<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryGaHeadApproval extends BaseWidget
{
    protected ?string $heading = 'Alat Tulis Kantor';
    protected static bool $isLazy = false;
    protected static ?int $sort = 9;
    protected function getColumns(): int
    {
        return 1;
    }
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need GA Head approval (GA Admin approved)
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN)
            ->where('type', OfficeStationeryStockRequest::TYPE_INCREASE)
            ->count();
        

        return [
            Stat::make('Stock Requests', $requestsCount)
                ->description('GA Admin Approved')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.request-list', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    ])
                )
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to IPC Heads
        return $user && $user->hasRole('Head') && $user->division && $user->division->initial === 'GA';
    }
}