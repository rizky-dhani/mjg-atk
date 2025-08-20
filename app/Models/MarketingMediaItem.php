<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class MarketingMediaItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'size',
        'category_id',
        'division_id',
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
     * Get the category that this marketing media belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class, 'category_id');
    }

    /**
     * Get the division that this marketing media belongs to.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class, 'division_id');
    }

    /**
     * Get the stock requests for this marketing media.
     */
    public function stockRequests(): HasMany
    {
        return $this->hasMany(MarketingMediaStockRequest::class, 'marketing_media_id');
    }

    /**
     * Get the stock per division records for this marketing media.
     */
    public function stockPerDivision(): HasMany
    {
        return $this->hasMany(MarketingMediaStockPerDivision::class, 'marketing_media_id');
    }

    /**
     * Get the stock level for a specific division.
     */
    public function getStockForDivision($divisionId)
    {
        $stock = $this->stockPerDivision()->where('division_id', $divisionId)->first();
        return $stock ? $stock->current_stock : 0;
    }
}
