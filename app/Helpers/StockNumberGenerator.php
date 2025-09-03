<?php

namespace App\Helpers;

use App\Models\CompanyDivision;
use App\Models\OfficeStationeryStockRequest;
use App\Models\MarketingMediaStockRequest;
use App\Models\OfficeStationeryStockUsage;
use App\Models\MarketingMediaStockUsage;
use Illuminate\Support\Facades\DB;

class StockNumberGenerator
{
    /**
     * Generate a unique request number for Office Stationery Stock Request
     *
     * @param int $divisionId
     * @return string
     */
    public static function generateOfficeStationeryRequestNumber(int $divisionId): string
    {
        $division = CompanyDivision::find($divisionId);
        $divisionInitial = $division ? $division->initial : 'DIV';
        
        // Use database locking to ensure we get a unique sequential number
        return DB::transaction(function () use ($divisionId, $divisionInitial) {
            // Lock the table to prevent race conditions
            $latestRequest = OfficeStationeryStockRequest::whereNotNull('request_number')
                ->where('division_id', $divisionId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
            
            if ($latestRequest) {
                // Extract the numeric part from the latest request number and increment it
                $parts = explode('-', $latestRequest->request_number);
                $latestNumber = intval(end($parts));
                $nextNumber = $latestNumber + 1;
            } else {
                // If no previous requests for this division, start with 1
                $nextNumber = 1;
            }
            
            return 'ATK-' . $divisionInitial . '-REQ-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        });
    }
    
    /**
     * Generate a unique request number for Marketing Media Stock Request
     *
     * @param int $divisionId
     * @return string
     */
    public static function generateMarketingMediaRequestNumber(int $divisionId): string
    {
        $division = CompanyDivision::find($divisionId);
        $divisionInitial = $division ? $division->initial : 'DIV';
        
        // Use database locking to ensure we get a unique sequential number
        return DB::transaction(function () use ($divisionId, $divisionInitial) {
            // Lock the table to prevent race conditions
            $latestRequest = MarketingMediaStockRequest::whereNotNull('request_number')
                ->where('division_id', $divisionId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
            
            if ($latestRequest) {
                // Extract the numeric part from the latest request number and increment it
                $parts = explode('-', $latestRequest->request_number);
                $latestNumber = intval(end($parts));
                $nextNumber = $latestNumber + 1;
            } else {
                // If no previous requests for this division, start with 1
                $nextNumber = 1;
            }
            
            return 'MM-' . $divisionInitial . '-REQ-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        });
    }
    
    /**
     * Generate a unique usage number for Office Stationery Stock Usage
     *
     * @param int $divisionId
     * @return string
     */
    public static function generateOfficeStationeryUsageNumber(int $divisionId): string
    {
        $division = CompanyDivision::find($divisionId);
        $divisionInitial = $division ? $division->initial : 'DIV';
        
        $latestUsage = OfficeStationeryStockUsage::whereNotNull('usage_number')
            ->where('division_id', $divisionId)
            ->orderBy('usage_number', 'desc')
            ->first();
        
        if ($latestUsage) {
            // Extract the numeric part from the latest usage number and increment it
            // Format is DIV-USAGE-00000001, so we need to extract the numeric part after the last dash
            $parts = explode('-', $latestUsage->usage_number);
            $latestNumber = intval(end($parts));
            $nextNumber = $latestNumber + 1;
        } else {
            // If no previous usages for this division, start with 1
            $nextNumber = 1;
        }
        
        return 'ATK-' . $divisionInitial . '-USAGE-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a unique usage number for Marketing Media Stock Usage
     *
     * @param int $divisionId
     * @return string
     */
    public static function generateMarketingMediaUsageNumber(int $divisionId): string
    {
        $division = CompanyDivision::find($divisionId);
        $divisionInitial = $division ? $division->initial : 'DIV';
        
        $latestUsage = MarketingMediaStockUsage::whereNotNull('usage_number')
            ->where('division_id', $divisionId)
            ->orderBy('usage_number', 'desc')
            ->first();
        
        if ($latestUsage) {
            // Extract the numeric part from the latest usage number and increment it
            // Format is MM-DIV-USAGE-00000001, so we need to extract the numeric part after the last dash
            $parts = explode('-', $latestUsage->usage_number);
            $latestNumber = intval(end($parts));
            $nextNumber = $latestNumber + 1;
        } else {
            // If no previous usages for this division, start with 1
            $nextNumber = 1;
        }
        
        return 'MM-' . $divisionInitial . '-USAGE-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }
}