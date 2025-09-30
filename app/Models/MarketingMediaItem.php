<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class MarketingMediaItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_stock',
        'marketing_media_category_id',
    ];

    protected $casts = [
        'max_stock' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Get the category that the marketing media item belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class, 'category_id');
    }

    /**
     * Get the marketing media division inventory settings for this item.
     */
    public function divisionSettings(): HasMany
    {
        return $this->hasMany(MarketingMediaDivisionInventorySetting::class, 'item_id');
    }

    /**
     * Get the stock requests for this item.
     */
    public function stockRequestItems(): HasMany
    {
        return $this->hasMany(MarketingMediaStockRequestItem::class, 'item_id');
    }

    /**
     * Get the division stocks for this item.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(MarketingMediaStockPerDivision::class, 'item_id');
    }

    /**
     * Get the prices for this item.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MarketingMediaItemPrice::class, 'item_id');
    }

    /**
     * Get the latest active price for this item.
     */
    public function getLatestPrice()
    {
        return $this->prices()->where(function ($query) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>', now());
        })
        ->orderBy('effective_date', 'desc')
        ->first();
    }
}
