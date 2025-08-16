<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MarketingMediaStockMovement
 *
 * @property int $id
 * @property int $marketing_media_id
 * @property int $division_id
 * @property string $movement_type
 * @property int $quantity
 * @property int $previous_stock
 * @property string $notes
 * @property string $movement_date
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read MarketingMedia $marketingMedia
 * @property-read CompanyDivision $division
 * @property-read User $creator
 * @property-read string $movement_type_label
 */
class MarketingMediaStockMovement extends Model
{
    protected $fillable = [
        'marketing_media_id',
        'division_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'movement_date',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'movement_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Get the marketing media
            $marketingMedia = MarketingMedia::find($model->marketing_media_id);
            if ($marketingMedia) {
                // Ensure the division_id matches the marketing media's division
                $model->division_id = $marketingMedia->division_id;
                
                // Store the previous stock before updating
                $model->previous_stock = $marketingMedia->current_stock;
                
                // Update the current stock based on movement type
                switch ($model->movement_type) {
                    case 'in':
                    case 'transfer':
                        $marketingMedia->current_stock += $model->quantity;
                        break;
                    case 'out':
                    case 'damaged':
                    case 'expired':
                        $marketingMedia->current_stock -= $model->quantity;
                        break;
                    case 'adjustment':
                        // Adjustments can be positive or negative
                        // Positive quantity increases stock, negative quantity decreases stock
                        $marketingMedia->current_stock += $model->quantity;
                        break;
                }
                
                $marketingMedia->save();
            }
            
            // If we couldn't find the marketing media or it doesn't have a division_id,
            // we should not allow the creation to proceed
            if (!$model->division_id) {
                throw new \Exception('Division ID is required for stock movements');
            }
        });
    }

    /**
     * Movement types
     */
    const MOVEMENT_TYPES = [
        'in' => 'Stock In',
        'out' => 'Stock Out',
        'transfer' => 'Transfer',
        'adjustment' => 'Adjustment',
        'damaged' => 'Damaged',
        'expired' => 'Expired',
    ];

    /**
     * Get the marketing media that this movement belongs to.
     */
    public function marketingMedia(): BelongsTo
    {
        return $this->belongsTo(MarketingMedia::class, 'marketing_media_id');
    }

    /**
     * Get the division that this movement belongs to.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class, 'division_id');
    }

    /**
     * Get the user who created this movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the movement type label.
     */
    public function getMovementTypeLabelAttribute(): string
    {
        return self::MOVEMENT_TYPES[$this->movement_type] ?? $this->movement_type;
    }
}
