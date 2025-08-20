<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'approval_mkt_head_id',
        'approval_mkt_head_at',
        'rejection_mkt_head_id',
        'rejection_mkt_head_at',
    ];
    
    protected $casts = [
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_ga_admin_at' => 'datetime',
        'rejection_ga_admin_at' => 'datetime',
        'approval_mkt_head_at' => 'datetime',
        'rejection_mkt_head_at' => 'datetime',
    ];

    const TYPE_DECREASE = 'decrease';
    
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
            if (empty($model->usage_number)) {
                // Get the division initial
                $division = CompanyDivision::find($model->division_id);
                $divisionInitial = $division ? $division->initial : 'DIV';
                
                // Get the latest usage by usage_number for this division to maintain proper sequence
                $latestUsage = MarketingMediaStockUsage::whereNotNull('usage_number')
                    ->where('division_id', $model->division_id)
                    ->orderBy('usage_number', 'desc')
                    ->first();
                
                if ($latestUsage) {
                    // Extract the numeric part from the latest usage number and increment it
                    // Format is DIV-MM-USG-00000001, so we need to extract the numeric part after the last dash
                    $parts = explode('-', $latestUsage->usage_number);
                    $latestNumber = intval(end($parts));
                    $nextNumber = $latestNumber + 1;
                } else {
                    // If no previous usages for this division, start with 1
                    $nextNumber = 1;
                }
                
                $model->usage_number = $divisionInitial . '-MM-USG-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
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
    public function needsMarketingSupportHeadApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }

    /**
     * Check if usage can be processed (stock reduced).
     */
    public function canProcessUsage(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_MKT_HEAD;
    }
    
    /**
     * Process stock adjustment for all items in this usage.
     * This method should be called when a usage is approved by all required parties:
     * Div Admin -> Div Head -> GA Admin -> Marketing Support Head.
     * It decreases stock based on the usage type.
     */
    public function processStockUsage(): void
    {
        if (!$this->canProcessUsage()) {
            throw new \Exception('Cannot process stock usage for this request. Not approved by Marketing Support Head.');
        }

        foreach ($this->items as $item) {
            // Get the current stock for this item in this division
            $stock = MarketingMediaStockPerDivision::where('division_id', $this->division_id)
                ->where('marketing_media_id', $item->marketing_media_id)
                ->first();

            if ($stock) {
                // Store previous stock level for reference
                $item->previous_stock = $stock->current_stock;
                
                // Decrease the stock by the requested quantity
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