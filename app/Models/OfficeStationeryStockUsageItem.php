<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeStationeryStockUsageItem extends Model
{
    protected $fillable = [
        'stock_usage_id',
        'item_id',
        'category_id',
        'quantity',
        'previous_stock',
        'new_stock',
        'notes',
        'price_id',
    ];
    
    protected $table = 'os_stock_usage_items';
    
    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    /**
     * Get the stock usage that owns this item.
     */
    public function stockUsage(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryStockUsage::class);
    }

    /**
     * Get the office stationery item for this usage item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItem::class);
    }

    /**
     * Get the office stationery category for this usage item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class);
    }

    /**
     * Get the price for this usage item.
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItemPrice::class, 'price_id');
    }
}
