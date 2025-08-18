<?php

namespace App\Filament\Widgets;

use App\Models\OfficeStationeryStockRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class DivisionOfficeStationeryStockRequestChart extends ChartWidget
{
    protected static ?string $heading = 'Division Stock Requests Overview';
    protected static ?string $description = 'Status distribution of stock requests in your division';
    protected static string $color = 'info';
    
    protected function getData(): array
    {
        $user = Auth::user();
        $divisionId = $user->division_id;
        
        // Get counts for different statuses
        $pendingCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_PENDING)
            ->count();
            
        $approvedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)
            ->count();
            
        $rejectedByHeadCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD)
            ->count();
            
        $deliveredCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_DELIVERED)
            ->count();
            
        $completedCount = OfficeStationeryStockRequest::where('division_id', $divisionId)
            ->where('status', OfficeStationeryStockRequest::STATUS_COMPLETED)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Number of Requests',
                    'data' => [$pendingCount, $approvedByHeadCount, $rejectedByHeadCount, $deliveredCount, $completedCount],
                    'backgroundColor' => [
                        'rgb(255, 205, 86)',  // Pending - yellow
                        'rgb(54, 162, 235)',  // Approved - blue
                        'rgb(255, 99, 132)',  // Rejected - red
                        'rgb(75, 192, 192)',  // Delivered - teal
                        'rgb(153, 102, 255)', // Completed - purple
                    ],
                ],
            ],
            'labels' => ['Pending', 'Approved by Head', 'Rejected by Head', 'Delivered', 'Completed'],
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
    
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Admin') && $user->division_id;
    }
}