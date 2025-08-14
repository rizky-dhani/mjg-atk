<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionInventorySetting extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'max_limit',
    ];

    protected $casts = [
        'max_limit' => 'integer',
    ];

    /**
     * Get the division that owns this setting.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Get the office stationery item for this setting.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryItem::class, 'item_id');
    }

    /**
     * Get the office stationery category for this setting.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeStationeryCategory::class, 'category_id');
    }
}
