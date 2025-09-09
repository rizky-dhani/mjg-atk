<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficeStationeryIpcAdminApproval extends BaseWidget
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
        $user = Auth::user();
        
        // Get requests that need IPC Admin approval
        $requestsCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->count();
        $stockAdjustmentCount = OfficeStationeryStockRequest::where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD)
            ->count();

        return [
            Stat::make('Pemasukan Barang', $requestsCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.request-list', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD
                    ])
                )
                ->description('Waiting for Approval')
                ->color('primary')
                ->icon('heroicon-o-document-text'),
            Stat::make('Pemasukan Barang', $stockAdjustmentCount)
                ->url(
                    route('filament.dashboard.resources.office-stationery-stock-requests.index', [
                        'tableFilters[status][values][0]' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD
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