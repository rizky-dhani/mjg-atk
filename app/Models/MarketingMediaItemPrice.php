<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingMediaItemPrice extends Model
{
    protected $fillable = [
        'item_id',
        'price',
        'effective_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'price' => 'integer',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the item that owns this price.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItem::class, 'item_id');
    }

    /**
     * Scope to get active prices (where end_date is null or in the future)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>', now());
        });
    }

    /**
     * Check if this price is currently active
     */
    public function isActive(): bool
    {
        if ($this->end_date && Carbon::parse($this->end_date)->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * Get the latest active price for an item
     */
    public static function getLatestPriceForItem(int $itemId)
    {
        return static::where('item_id', $itemId)
                     ->active()
                     ->orderBy('effective_date', 'desc')
                     ->first();
    }
}