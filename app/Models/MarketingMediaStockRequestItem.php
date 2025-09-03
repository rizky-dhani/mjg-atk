<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingMediaStockRequestItem extends Model
{
    protected $fillable = [
        'stock_request_id',
        'item_id',
        'category_id',
        'quantity',
        'adjusted_quantity',
        'previous_stock',
        'new_stock',
        'notes',
    ];
    protected $table = 'mm_stock_request_items';
    protected $casts = [
        'quantity' => 'integer',
        'adjusted_quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    /**
     * Get the stock request that owns this item.
     */
    public function stockRequest(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaStockRequest::class);
    }

    /**
     * Get the marketing media item for this request item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItem::class);
    }

    /**
     * Get the marketing media category for this request item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class);
    }
}
