<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MarketingMediaStockRequest
 *
 * @property int $id
 * @property int $marketing_media_id
 * @property int $division_id
 * @property string $type
 * @property int $quantity
 * @property int $previous_stock
 * @property string $notes
 * @property int $created_by
 * @property string $status
 * @property string $rejection_reason
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read MarketingMedia $marketingMedia
 * @property-read CompanyDivision $division
 * @property-read User $creator
 */
class MarketingMediaStockRequest extends Model
{
    protected $fillable = [
        'marketing_media_id',
        'division_id',
        'type',
        'quantity',
        'previous_stock',
        'created_by',
        'status',
        'rejection_reason',
        'approval_head_id',
        'approval_head_at',
        'rejection_head_id',
        'rejection_head_at',
        'approval_admin_ga_id',
        'approval_admin_ga_at',
        'rejection_admin_ga_id',
        'rejection_admin_ga_at',
        'approval_mkt_head_id',
        'approval_mkt_head_at',
        'rejection_mkt_head_id',
        'rejection_mkt_head_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_admin_ga_at' => 'datetime',
        'rejection_admin_ga_at' => 'datetime',
        'approval_mkt_head_at' => 'datetime',
        'rejection_mkt_head_at' => 'datetime',
    ];

    const TYPE_INCREASE = 'increase';
    const TYPE_REDUCTION = 'reduction';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_GA_ADMIN = 'approved_by_ga_admin';
    const STATUS_REJECTED_BY_GA_ADMIN = 'rejected_by_ga_admin';
    const STATUS_APPROVED_BY_MKT_HEAD = 'approved_by_mkt_head';
    const STATUS_REJECTED_BY_MKT_HEAD = 'rejected_by_mkt_head';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Set default status
            if (empty($model->status)) {
                $model->status = self::STATUS_PENDING;
            }
            
            // Get the marketing media
            $marketingMedia = MarketingMedia::find($model->marketing_media_id);
            if ($marketingMedia) {
                // Ensure the division_id matches the marketing media's division
                $model->division_id = $marketingMedia->division_id;
                
                // Store the previous stock before updating
                $model->previous_stock = $marketingMedia->current_stock;
                
                // For stock reduction, we don't update the stock immediately
                // Stock will be updated only when the request is fully approved
                if ($model->type !== self::TYPE_REDUCTION) {
                    // Update the current stock based on movement type for non-reduction movements
                    switch ($model->type) {
                        case self::TYPE_INCREASE:
                            $marketingMedia->current_stock += $model->quantity;
                            break;
                    }
                    
                    $marketingMedia->save();
                }
            }
            
            // If we couldn't find the marketing media or it doesn't have a division_id,
            // we should not allow the creation to proceed
            if (!$model->division_id) {
                throw new \Exception('Division ID is required for stock movements');
            }
        });
    }

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
     * Get the user who approved this by division head.
     */
    public function divisionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_head_id');
    }

    /**
     * Get the user who rejected this by division head.
     */
    public function rejectionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_head_id');
    }

    /**
     * Get the GA Admin who approved this.
     */
    public function gaAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_admin_ga_id');
    }

    /**
     * Get the GA Admin who rejected this.
     */
    public function rejectionGaAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_admin_ga_id');
    }

    /**
     * Get the Marketing Support Head who approved this.
     */
    public function marketingSupportHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_mkt_head_id');
    }

    /**
     * Get the Marketing Support Head who rejected this.
     */
    public function rejectionMarketingSupportHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_mkt_head_id');
    }

    /**
     * Check if this is a stock reduction request.
     */
    public function isReduction(): bool
    {
        return $this->type === self::TYPE_REDUCTION;
    }

    /**
     * Check if this is a stock increase request.
     */
    public function isIncrease(): bool
    {
        return $this->type === self::TYPE_INCREASE;
    }

    /**
     * Check if request needs Division Head approval.
     */
    public function needsDivisionHeadApproval(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request needs GA Admin approval.
     */
    public function needsGaAdminApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_HEAD;
    }

    /**
     * Check if request needs Marketing Support Head approval.
     */
    public function needsMarketingSupportHeadApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }

    /**
     * Process stock reduction.
     * This method should be called when a reduction request is approved by all required parties.
     */
    public function processStockReduction(): void
    {
        if (!$this->canProcessReduction()) {
            throw new \Exception('Cannot process stock reduction for this request.');
        }

        // Get the marketing media
        $marketingMedia = $this->marketingMedia;
        
        if ($marketingMedia) {
            // Reduce the stock by the requested quantity
            $marketingMedia->current_stock -= $this->quantity;
            
            // Ensure stock doesn't go below zero
            if ($marketingMedia->current_stock < 0) {
                $marketingMedia->current_stock = 0;
            }
            
            // Save the new stock level
            $marketingMedia->save();
        }
        
        // Update request status to completed
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Check if reduction request can be processed (stock reduced).
     */
    public function canProcessReduction(): bool
    {
        return $this->isReduction() && $this->status === self::STATUS_APPROVED_BY_MKT_HEAD;
    }
}
