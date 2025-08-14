<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeStationeryCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the office stationery items in this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OfficeStationeryItem::class, 'category_id');
    }
}
