<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OfficeStationeryStockPerDivision;

class OfficeStationeryStockRequest extends Model
{
    protected $table = 'stock_requests';
    protected $fillable = [
        'request_number',
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
        'approval_ipc_id',
        'approval_ipc_at',
        'rejection_ipc_id',
        'rejection_ipc_at',
        'approval_ipc_head_id',
        'approval_ipc_head_at',
        'rejection_ipc_head_id',
        'rejection_ipc_head_at',
        'delivered_by',
        'delivered_at',
        'approval_stock_adjustment_id',
        'approval_stock_adjustment_at',
        'rejection_stock_adjustment_id',
        'rejection_stock_adjustment_at',
        'approval_ga_admin_id',
        'approval_ga_admin_at',
        'rejection_ga_admin_id',
        'rejection_ga_admin_at',
        'approval_ga_head_id',
        'approval_ga_head_at',
        'rejection_ga_head_id',
        'rejection_ga_head_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_ipc_at' => 'datetime',
        'rejection_ipc_at' => 'datetime',
        'approval_ipc_head_at' => 'datetime',
        'rejection_ipc_head_at' => 'datetime',
        'approval_stock_adjustment_at' => 'datetime',
        'rejection_stock_adjustment_at' => 'datetime',
        'approval_ga_admin_at' => 'datetime',
        'rejection_ga_admin_at' => 'datetime',
        'approval_ga_head_at' => 'datetime',
        'rejection_ga_head_at' => 'datetime',
    ];

    const TYPE_INCREASE = 'increase';
    const TYPE_REDUCTION = 'reduction';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_IPC = 'approved_by_ipc';
    const STATUS_REJECTED_BY_IPC = 'rejected_by_ipc';
    const STATUS_APPROVED_BY_IPC_HEAD = 'approved_by_ipc_head';
    const STATUS_REJECTED_BY_IPC_HEAD = 'rejected_by_ipc_head';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_APPROVED_STOCK_ADJUSTMENT = 'approved_stock_adjustment';
    const STATUS_APPROVED_BY_GA_ADMIN = 'approved_by_ga_admin';
    const STATUS_REJECTED_BY_GA_ADMIN = 'rejected_by_ga_admin';
    const STATUS_APPROVED_BY_GA_HEAD = 'approved_by_ga_head';
    const STATUS_REJECTED_BY_GA_HEAD = 'rejected_by_ga_head';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $latestRequest = OfficeStationeryStockRequest::orderBy('id', 'desc')->first();
                $nextId = $latestRequest ? $latestRequest->id + 1 : 1;
                $model->request_number = 'REQ-' . str_pad($nextId, 8, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the division that owns this request.
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
     * Get the user who approved this.
     */
    public function ipcStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_ipc_id');
    }

    /**
     * Get the user who rejected this.
     */
    public function rejectionIpc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_ipc_id');
    }

    /**
     * Get the IPC Head who approved this.
     */
    public function ipcHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_ipc_head_id');
    }

    /**
     * Get the IPC Head who rejected this.
     */
    public function rejectionIpcHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_ipc_head_id');
    }

    /**
     * Get the user who approved the stock adjustment.
     */
    public function approvalStockAdjustmentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_stock_adjustment_id');
    }

    /**
     * Get the user who rejected the stock adjustment.
     */
    public function rejectionStockAdjustmentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_stock_adjustment_id');
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
     * Get the GA Head who approved this.
     */
    public function gaHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_ga_head_id');
    }

    /**
     * Get the GA Head who rejected this.
     */
    public function rejectionGaHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_ga_head_id');
    }

    /**
     * Get the user who delivered this (IPC Staff).
     */
    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    /**
     * Get the items in this request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockRequestItem::class);
    }

    /**
     * Check if this is a stock increase request.
     */
    public function isIncrease(): bool
    {
        return $this->type === self::TYPE_INCREASE;
    }

    /**
     * Check if this is a stock reduction request.
     */
    public function isReduction(): bool
    {
        return $this->type === self::TYPE_REDUCTION;
    }

    /**
     * Check if request needs IPC approval (only for increase requests).
     */
    public function needsIpcApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_HEAD;
    }

    /**
     * Check if request needs IPC Head approval (only for increase requests).
     */
    public function needsIpcHeadApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_IPC;
    }

    /**
     * Check if request can be delivered (only for increase requests).
     */
    public function canBeDelivered(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_IPC_HEAD;
    }

    /**
     * Check if request needs stock adjustment approval (only for increase requests).
     */
    public function needsStockAdjustmentApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if request needs GA Admin approval (only for increase requests).
     */
    public function needsGaAdminApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_STOCK_ADJUSTMENT;
    }

    /**
     * Check if request needs GA Head approval.
     */
    public function needsGaHeadApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }
    
    /**
     * Process stock reduction for all items in this request.
     * This method should be called when a reduction request is approved by all required parties.
     */
    public function processStockReduction(): void
    {
        if (!$this->canProcessReduction()) {
            throw new \Exception('Cannot process stock reduction for this request.');
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
        
        // Update request status to completed
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Check if reduction request needs Division Head approval.
     */
    public function needsDivisionHeadApprovalForReduction(): bool
    {
        return $this->isReduction() && $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if reduction request needs GA Admin approval.
     */
    public function needsGaAdminApprovalForReduction(): bool
    {
        return $this->isReduction() && $this->status === self::STATUS_APPROVED_BY_HEAD;
    }

    /**
     * Check if reduction request needs GA Head approval.
     */
    public function needsGaHeadApprovalForReduction(): bool
    {
        return $this->isReduction() && $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }

    /**
     * Check if reduction request can be processed (stock reduced).
     */
    public function canProcessReduction(): bool
    {
        return $this->isReduction() && $this->status === self::STATUS_APPROVED_BY_GA_HEAD;
    }
    
}