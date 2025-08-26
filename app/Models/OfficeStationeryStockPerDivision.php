<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeStationeryStockPerDivision extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'office_stationery_category_id',
        'current_stock',
    ];
    protected $table = 'os_stocks_per_division';
    protected $casts = [
        'current_stock' => 'integer',
    ];

    /**
     * Get the division that owns this office stationery stock.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Get the office stationery item for this office stationery stock.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItem::class, 'item_id');
    }

    /**
     * Get the office stationery item category for this office stationery stock.
     */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class, 'category_id');
    }

    /**
     * Get the division inventory setting for this office stationery stock.
     */
    public function setting()
    {
        return DivisionInventorySetting::where('division_id', $this->division_id)
            ->where('item_id', $this->item_id)
            ->first();
    }

    /**
     * Get the maximum limit for this item in this division.
     */
    public function maxLimit()
    {
        $setting = $this->setting();
        return $setting ? $setting->max_limit : null;
    }

    /**
     * Check if current stock exceeds the maximum limit.
     */
    public function isOverLimit(): bool
    {
        $maxLimit = $this->maxLimit();
        return $maxLimit !== null && $this->current_stock > $maxLimit;
    }

    /**
     * Check if current stock is at the maximum limit.
     */
    public function isAtLimit(): bool
    {
        $maxLimit = $this->maxLimit();
        return $maxLimit !== null && $this->current_stock == $maxLimit;
    }

    /**
     * Check if current stock is within the maximum limit.
     */
    public function isWithinLimit(): bool
    {
        $maxLimit = $this->maxLimit();
        return $maxLimit === null || $this->current_stock <= $maxLimit;
    }

    /**
     * Stock usages belonging to the same division.
     * Additional item filtering will be applied in the RelationManager.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockRequest::class, 'division_id', 'division_id');
    }
    public function usages(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockUsage::class, 'division_id', 'division_id');
    }
}
