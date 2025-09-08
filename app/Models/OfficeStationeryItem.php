<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OfficeStationeryItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_stock',
        'office_stationery_category_id',
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
     * Get the category that the office stationery item belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class, 'category_id');
    }

    /**
     * Get the office stationery division inventory settings for this item.
     */
    public function divisionSettings(): HasMany
    {
        return $this->hasMany(OfficeStationeryDivisionInventorySetting::class, 'item_id');
    }

    /**
     * Get the stock requests for this item.
     */
    public function stockRequestItems(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockRequestItem::class, 'item_id');
    }

    /**
     * Get the division stocks for this item.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockPerDivision::class, 'item_id');
    }
}
