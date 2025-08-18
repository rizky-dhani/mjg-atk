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
            ->orderBy('movement_date', 'desc')
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
     * Recalculate current stock based on all requests.
     */
    public function recalculateStock()
    {
        $requests = $this->stockRequests()->orderBy('created_at')->get();
        $currentStock = 0;

        foreach ($requests as $request) {
            // Store the previous stock before updating
            $request->previous_stock = $currentStock;
            $request->save();

            // Update current stock based on request type
            switch ($request->movement_type) {
                case 'in':
                case 'transfer':
                    $currentStock += $request->quantity;
                    break;
                case 'out':
                case 'damaged':
                case 'expired':
                    $currentStock -= $request->quantity;
                    break;
                case 'adjustment':
                    // Adjustments can be positive or negative
                    // Positive quantity increases stock, negative quantity decreases stock
                    $currentStock += $request->quantity;
                    break;
            }
        }

        $this->current_stock = $currentStock;
        $this->save();

        return $this;
    }
}
