<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingMediaStockPerDivision extends Model
{
    protected $fillable = [
        'marketing_media_id',
        'division_id',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
    ];

    /**
     * Get the marketing media item that this stock belongs to.
     */
    public function marketingMediaItem(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItem::class);
    }

    /**
     * Get the division that this stock belongs to.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }
}