<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeStationeryDivisionInventorySetting extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'max_limit',
    ];

    protected $table = 'os_division_inventory_settings';

    protected $casts = [
        'max_limit' => 'integer',
    ];

    /**
     * Get the division that owns this setting.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Get the office stationery item for this setting.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItem::class, 'item_id');
    }

    /**
     * Get the office stationery category for this setting.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class, 'category_id');
    }

    /**
     * Get the current stock level for this item in this division.
     */
    public function currentStock()
    {
        $stock = OfficeStationeryStockPerDivision::where('division_id', $this->division_id)
            ->where('item_id', $this->item_id)
            ->first();
        return $stock ? $stock->current_stock : 0;
    }

    /**
     * Check if current stock exceeds the maximum limit.
     */
    public function isOverLimit(): bool
    {
        $currentStock = $this->currentStock();
        return $currentStock > $this->max_limit;
    }

    /**
     * Check if current stock is at the maximum limit.
     */
    public function isAtLimit(): bool
    {
        $currentStock = $this->currentStock();
        return $currentStock == $this->max_limit;
    }

    /**
     * Check if current stock is within the maximum limit.
     */
    public function isWithinLimit(): bool
    {
        $currentStock = $this->currentStock();
        return $currentStock <= $this->max_limit;
    }
}