<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MarketingMediaItemPrice;

class MarketingMediaStockUsageItem extends Model
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
    
    protected $table = 'mm_stock_usage_items';
    
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
        return $this->belongsTo(MarketingMediaStockUsage::class);
    }

    /**
     * Get the marketing media item for this usage item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItem::class);
    }

    /**
     * Get the marketing media item category for this usage item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class);
    }

    /**
     * Get the price for this usage item.
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItemPrice::class, 'price_id');
    }
}
