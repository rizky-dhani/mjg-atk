<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryIpcAdminApproval extends BaseWidget
{
    protected static ?string $heading = 'Office Stationery Stock Requests';
    protected static bool $isLazy = false;
    protected static ?int $sort = 8;
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get requests that need IPC Admin approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->count();

        return [
            Stat::make('Waiting for Approval', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][value]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD
                    ])
                )
                ->description('Stock Requests')
->color('primary')
                ->icon('heroicon-o-document-text'),
        ];
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        // Only show to GA Admins
        return $user && $user->hasRole('Admin') && $user->division_id && $user->division?->name == 'Import and Purchasing';
    }
}