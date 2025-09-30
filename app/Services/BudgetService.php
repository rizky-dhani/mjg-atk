<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\OfficeStationeryItem;
use App\Models\MarketingMediaItem;
// Remove this line - we don't need ItemPrice anymore

class BudgetService
{
    /**
     * Adjust budget by decreasing current amount (only on Out/usage)
     */
    public function adjustBudget(int $divisionId, $amount, string $type): void
    {
        $budget = Budget::where('division_id', $divisionId)
                    ->where('type', $type)
                    ->first();

        if ($budget) {
            $budget->deductAmount($amount);
        }
    }

    /**
     * Add stock and update related budget if needed
     */
    public function addStock($item, int $qty): void
    {
        // This method would handle adding stock to an item
        // For now, we'll just update the stock quantity in the relevant table
        // The actual implementation would depend on the specific stock model
    }

    /**
     * Use stock and deduct from the correct budget type
     */
    public function useStock($item, int $qty): void
    {
        // This method would handle using stock from an item
        // and deduct the cost from the appropriate budget
    }

    /**
     * Calculate the total cost for items in a usage
     */
    public function calculateUsageCost($usageItems)
    {
        $totalCost = 0;

        foreach ($usageItems as $usageItem) {
            $item = null;
            
            // Determine if this is an OfficeStationeryItem or MarketingMediaItem
            if (str_contains(get_class($usageItem->item), 'OfficeStationery')) {
                $item = $usageItem->item;
                // Get the latest active price for this OfficeStationeryItem
                $itemPrice = \App\Models\OfficeStationeryItemPrice::where('item_id', $item->id)
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>', now());
                    })
                    ->orderBy('effective_date', 'desc')
                    ->first();
                
                if ($itemPrice) {
                    $totalCost += $itemPrice->price * $usageItem->quantity;
                }
            } elseif (str_contains(get_class($usageItem->item), 'MarketingMedia')) {
                $item = $usageItem->item;
                $itemPrice = \App\Models\MarketingMediaItemPrice::where('item_id', $item->id)
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>', now());
                    })
                    ->orderBy('effective_date', 'desc')
                    ->first();
                
                if ($itemPrice) {
                    $totalCost += $itemPrice->price * $usageItem->quantity;
                }
            }
        }

        return $totalCost;
    }

    /**
     * Check if a division has sufficient budget for a specific type
     */
    public function hasSufficientBudget(int $divisionId, string $type, $amount): bool
    {
        $budget = Budget::where('division_id', $divisionId)
                    ->where('type', $type)
                    ->first();

        if (!$budget) {
            return false;
        }

        return $budget->hasSufficientFunds($amount);
    }

    /**
     * Get the remaining budget for a division and type
     */
    public function getRemainingBudget(int $divisionId, string $type)
    {
        $budget = Budget::where('division_id', $divisionId)
                    ->where('type', $type)
                    ->first();

        if (!$budget) {
            return 0;
        }

        return $budget->getRemainingAmount();
    }
}