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
    protected $table = 'office_stationery_stocks_per_division';
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
     * Stock requests belonging to the same division.
     * Additional item filtering will be applied in the RelationManager.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'division_id', 'division_id');
    }

    /**
     * Stock usages belonging to the same division.
     * Additional item filtering will be applied in the RelationManager.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(StockUsage::class, 'division_id', 'division_id');
    }
}
