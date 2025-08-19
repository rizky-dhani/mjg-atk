<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeStationeryStockUsage extends Model
{
    protected $table = 'os_stock_usages';
    
    protected $fillable = [
        'usage_number',
        'division_id',
        'requested_by',
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
        'approval_hcg_head_id',
        'approval_hcg_head_at',
        'rejection_hcg_head_id',
        'rejection_hcg_head_at',
    ];
    
    protected $casts = [
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_ga_admin_at' => 'datetime',
        'rejection_ga_admin_at' => 'datetime',
        'approval_hcg_head_at' => 'datetime',
        'rejection_hcg_head_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_GA_ADMIN = 'approved_by_ga_admin';
    const STATUS_REJECTED_BY_GA_ADMIN = 'rejected_by_ga_admin';
    const STATUS_APPROVED_BY_SUPERVISOR_MARKETING = 'approved_by_mkt_head';
    const STATUS_REJECTED_BY_SUPERVISOR_MARKETING = 'rejected_by_mkt_head';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->usage_number)) {
                // Get the division initial
                $division = CompanyDivision::find(auth()->user()->division_id);
                $divisionInitial = $division ? $division->initial : 'DIV';
                
                // Get the latest usage by usage_number for this division to maintain proper sequence
                $latestUsage = OfficeStationeryStockUsage::whereNotNull('usage_number')
                    ->where('division_id', auth()->user()->division_id)
                    ->orderBy('usage_number', 'desc')
                    ->first();
                
                if ($latestUsage) {
                    // Extract the numeric part from the latest usage number and increment it
                    // Format is DIV-USE-00000001, so we need to extract the numeric part after the last dash
                    $parts = explode('-', $latestUsage->usage_number);
                    $latestNumber = intval(end($parts));
                    $nextNumber = $latestNumber + 1;
                } else {
                    // If no previous usages for this division, start with 1
                    $nextNumber = 1;
                }
                
                $model->usage_number = $divisionInitial . '-USAGE-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
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
     * Get the Supervisor/Head Marketing Support who approved this.
     */
    public function supervisorMarketing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_hcg_head_id');
    }

    /**
     * Get the Supervisor/Head Marketing Support who rejected this.
     */
    public function rejectionSupervisorMarketing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_hcg_head_id');
    }

    /**
     * Get the items in this usage.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockUsageItem::class, 'stock_usage_id');
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
     * Check if usage needs Supervisor/Head Marketing Support approval.
     */
    public function needsSupervisorMarketingApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }

    /**
     * Check if usage can be processed (stock reduced).
     */
    public function canProcessUsage(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_SUPERVISOR_MARKETING;
    }
    
    /**
     * Process stock reduction for all items in this usage.
     * This method should be called when a usage is approved by all required parties.
     */
    public function processStockUsage(): void
    {
        if (!$this->canProcessUsage()) {
            throw new \Exception('Cannot process stock usage for this request.');
        }

        foreach ($this->items as $item) {
            // Get the current stock for this item in this division
            $stock = OfficeStationeryStockPerDivision::where('division_id', $this->division_id)
                ->where('item_id', $item->item_id)
                ->first();

            if ($stock) {
                // Store previous stock level for reference
                $item->previous_stock = $stock->current_stock;
                
                // Reduce the stock by the requested quantity
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
