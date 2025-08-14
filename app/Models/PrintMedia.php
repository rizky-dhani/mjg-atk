<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class PrintMedia extends Model
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
     * Get the category that this print media belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PrintMediaCategory::class, 'category_id');
    }

    /**
     * Get the division that this print media belongs to.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class, 'division_id');
    }

    /**
     * Get the stock movements for this print media.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(PrintMediaStockMovement::class, 'print_media_id');
    }

    /**
     * Get the latest stock movement for this print media within the same division.
     */
    public function getLatestMovementAttribute()
    {
        return $this->stockMovements()
            ->where('division_id', $this->division_id)
            ->orderBy('movement_date', 'desc')
            ->first();
    }

    /**
     * Get the previous stock based on the last stock movement.
     */
    public function getPreviousStockAttribute()
    {
        $lastMovement = $this->stockMovements()->latest('created_at')->first();
        return $lastMovement ? $lastMovement->previous_stock : 0;
    }

    /**
     * Recalculate current stock based on all movements.
     */
    public function recalculateStock()
    {
        $movements = $this->stockMovements()->orderBy('created_at')->get();
        $currentStock = 0;

        foreach ($movements as $movement) {
            // Store the previous stock before updating
            $movement->previous_stock = $currentStock;
            $movement->save();

            // Update current stock based on movement type
            switch ($movement->movement_type) {
                case 'in':
                case 'transfer':
                    $currentStock += $movement->quantity;
                    break;
                case 'out':
                case 'damaged':
                case 'expired':
                    $currentStock -= $movement->quantity;
                    break;
                case 'adjustment':
                    // Adjustments can be positive or negative
                    // Positive quantity increases stock, negative quantity decreases stock
                    $currentStock += $movement->quantity;
                    break;
            }
        }

        $this->current_stock = $currentStock;
        $this->save();

        return $this;
    }
}
