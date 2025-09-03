<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\StockNumberGenerator;

class MarketingMediaStockUsage extends Model
{
    protected $table = 'mm_stock_usages';
    
    protected $fillable = [
        'usage_number',
        'division_id',
        'requested_by',
        'type',
        'status',
        'notes',
        'rejection_reason',
        'approval_head_id',
        'approval_head_at',
        'rejection_head_id',
        'rejection_head_at',
        'approval_ga_admin_id',
        'approval_ga_admin_at',
        'rejection_ga_admin_id',
        'rejection_ga_admin_at',
        'approval_marketing_head_id',
        'approval_marketing_head_at',
        'rejection_marketing_head_id',
        'rejection_marketing_head_at',
    ];
    
    protected $casts = [
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_ga_admin_at' => 'datetime',
        'rejection_ga_admin_at' => 'datetime',
        'approval_marketing_head_at' => 'datetime',
        'rejection_marketing_head_at' => 'datetime',
    ];
    
    const TYPE_DECREASE = 'decrease';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_GA_ADMIN = 'approved_by_ga_admin';
    const STATUS_REJECTED_BY_GA_ADMIN = 'rejected_by_ga_admin';
    const STATUS_REJECTED_BY_MKT_HEAD = 'rejected_by_marketing_support_head';
    const STATUS_APPROVED_BY_MKT_HEAD = 'approved_by_marketing_support_head';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Set default type to decrease for stock usage
            if (empty($model->type)) {
                $model->type = self::TYPE_DECREASE;
            }
            
            if (empty($model->usage_number)) {
                // Generate usage number using the helper
                $model->usage_number = StockNumberGenerator::generateMarketingMediaUsageNumber($model->division_id);
            }
        });
    }

    /**
     * Get the division that owns this usage.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Get the user who requested this.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved this.
     */
    public function divisionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_head_id');
    }

    /**
     * Get the user who rejected this.
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
        return $this->belongsTo(User::class, 'approval_ga_admin_id');
    }

    /**
     * Get the GA Admin who rejected this.
     */
    public function rejectionGaAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_ga_admin_id');
    }

    /**
     * Get the Marketing Support Head who approved this.
     */
    public function marketingSupportHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_marketing_head_id');
    }

    /**
     * Get the Marketing Support Head who rejected this.
     */
    public function rejectionMarketingSupportHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_marketing_head_id');
    }

    /**
     * Get the items in this usage.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MarketingMediaStockUsageItem::class, 'stock_usage_id');
    }

    /**
     * Check if usage needs Division Head approval.
     */
    public function needsDivisionHeadApproval(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if usage needs GA Admin approval.
     */
    public function needsGaAdminApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_HEAD;
    }

    /**
     * Check if usage needs Marketing Support Head approval.
     */
    public function needsMarketingHeadApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }

    /*** Check if usage can be processed (stock reduced).*/    
    public function canProcessUsage(): bool    
    {        
        return $this->status === self::STATUS_APPROVED_BY_MKT_HEAD;    
    }
    
    /**
     * Process stock adjustment for all items in this usage.
     * This method should be called when a usage is approved by all required parties:
     * Div Admin -> Div Head -> GA Admin -> Marketing Support Head.
     * It can either increase or decrease stock based on the usage type.
     */
    public function processStockUsage(): void
    {
        if (!$this->canProcessUsage()) {
            throw new \Exception('Cannot process stock usage for this request. Not approved by Marketing Support Head.');
        }

        foreach ($this->items as $item) {
            // Get the current stock for this item in this division
            $stock = MarketingMediaStockPerDivision::where('division_id', $this->division_id)
                ->where('item_id', $item->item_id)
                ->first();

            if ($stock) {
                // Store previous stock level for reference
                $item->previous_stock = $stock->current_stock;
                
                // Decrease the stock by the requested quantity (default behavior)
                $stock->current_stock -= $item->quantity;
                
                // Ensure stock doesn't go below zero
                if ($stock->current_stock < 0) {
                    $stock->current_stock = 0;
                }
                
                // Save the new stock level
                $stock->save();
                
                // Store new stock level for reference
                $item->new_stock = $stock->current_stock;
                $item->save();
            }
        }
        
        // Update usage status to completed
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }
}