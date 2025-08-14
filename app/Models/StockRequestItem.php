<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockRequestItem extends Model
{
    protected $fillable = [
        'stock_request_id',
        'item_id',
        'category_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the stock request that owns this item.
     */
    public function stockRequest(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class);
    }

    /**
     * Get the office stationery item for this request item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItem::class);
    }

    /**
     * Get the office stationery category for this request item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class);
    }
}
