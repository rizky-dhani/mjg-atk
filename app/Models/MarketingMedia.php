<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class MarketingMedia extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'size',
        'category_id',
        'division_id',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
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
     * Get the latest stock request for this marketing media within the same division.
     */
    public function getLatestRequestAttribute()
    {
        return $this->stockRequests()
            ->where('division_id', $this->division_id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the previous stock based on the last stock movement.
     */
    public function getPreviousStockAttribute()
    {
        $lastRequest = $this->stockRequests()->latest('created_at')->first();
        return $lastRequest ? $lastRequest->previous_stock : 0;
    }

    /**
     * Recalculate current stock based on all completed requests.
     */
    public function recalculateStock()
    {
        $requests = $this->stockRequests()
            ->where('status', MarketingMediaStockRequest::STATUS_COMPLETED)
            ->orderBy('created_at')
            ->get();
            
        $currentStock = 0;

        foreach ($requests as $request) {
            // Update current stock based on request type
            if ($request->type === MarketingMediaStockRequest::TYPE_INCREASE) {
                $currentStock += $request->quantity;
            } elseif ($request->type === MarketingMediaStockRequest::TYPE_REDUCTION) {
                $currentStock -= $request->quantity;
            }
        }

        $this->current_stock = $currentStock;
        $this->save();

        return $this;
    }
}
