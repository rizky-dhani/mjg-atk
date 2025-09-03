<?php

namespace App\Models;

use App\Enums\StockRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingMediaStockRequest extends Model
{
    use HasFactory;

    protected $table = 'mm_stock_requests';
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
        'approval_second_ipc_head_id',
        'approval_second_ipc_head_at',
        'rejection_second_ipc_head_id',
        'rejection_second_ipc_head_at',
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
        'approval_second_ipc_head_at' => 'datetime',
        'rejection_second_ipc_head_at' => 'datetime',
        'approval_ga_admin_at' => 'datetime',
        'rejection_ga_admin_at' => 'datetime',
        'approval_marketing_head_at' => 'datetime',
        'rejection_marketing_head_at' => 'datetime',
    ];

    const TYPE_INCREASE = 'increase';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_IPC = 'approved_by_ipc';
    const STATUS_REJECTED_BY_IPC = 'rejected_by_ipc';
    const STATUS_APPROVED_BY_IPC_HEAD = 'approved_by_ipc_head';
    const STATUS_REJECTED_BY_IPC_HEAD = 'rejected_by_ipc_head';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_APPROVED_STOCK_ADJUSTMENT = 'approved_stock_adjustment';
    const STATUS_APPROVED_BY_SECOND_IPC_HEAD = 'approved_by_second_ipc_head';
    const STATUS_REJECTED_BY_SECOND_IPC_HEAD = 'rejected_by_second_ipc_head';
    const STATUS_APPROVED_BY_GA_ADMIN = 'approved_by_ga_admin';
    const STATUS_REJECTED_BY_GA_ADMIN = 'rejected_by_ga_admin';
    const STATUS_APPROVED_BY_MARKETING_SUPPORT_HEAD = 'approved_by_marketing_support_head';
    const STATUS_REJECTED_BY_MARKETING_SUPPORT_HEAD = 'rejected_by_marketing_support_head';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                // Get the division initial
                $division = CompanyDivision::find($model->division_id);
                $divisionInitial = $division ? $division->initial : 'DIV';
                
                // Use database locking to ensure we get a unique sequential number
                DB::transaction(function () use ($model, $divisionInitial) {
                    // Lock the table to prevent race conditions
                    $latestRequest = MarketingMediaStockRequest::whereNotNull('request_number')
                        ->where('division_id', $model->division_id)
                        ->orderByDesc('id')
                        ->lockForUpdate() // This will lock the rows until transaction completes
                        ->first();
                    
                    if ($latestRequest) {
                        // Extract the numeric part from the latest request number and increment it
                        $parts = explode('-', $latestRequest->request_number);
                        $latestNumber = intval(end($parts));
                        $nextNumber = $latestNumber + 1;
                    } else {
                        // If no previous requests for this division, start with 1
                        $nextNumber = 1;
                    }
                    
                    $model->request_number = 'MM' . $divisionInitial . '-REQ-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
                });
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
     * Get the IPC Admin who approved this.
     */
    public function ipcAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_ipc_id');
    }

    /**
     * Get the IPC Admin who rejected this.
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
     * Get the second IPC Head who approved this.
     */
    public function secondIpcHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_second_ipc_head_id');
    }

    /**
     * Get the second IPC Head who rejected this.
     */
    public function rejectionSecondIpcHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejection_second_ipc_head_id');
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
     * Get the user who delivered this (IPC Admin).
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
        return $this->hasMany(MarketingMediaStockRequestItem::class);
    }

    /**
     * Check if this is a stock increase request.
     */
    public function isIncrease(): bool
    {
        return $this->type === self::TYPE_INCREASE;
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
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_SECOND_IPC_HEAD;
    }

    /**
     * Check if request needs second IPC Head approval (only for increase requests).
     */
    public function needsSecondIpcHeadApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_STOCK_ADJUSTMENT;
    }

    /**
     * Check if request needs HCG Head approval.
     */
    public function needsMarketingHeadApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_GA_ADMIN;
    }
}