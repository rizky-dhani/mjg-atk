<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockRequest extends Model
{
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
                $model->request_number = 'REQ-' . now()->timezone('Asia/Jakarta')->format('Ymd') . '-' . str_pad(StockRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
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
     * Check if request needs IPC approval (only for increase requests).
     */
    public function needsIpcApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_HEAD;
    }

    /**
     * Check if request needs IPC Head approval.
     */
    public function needsIpcHeadApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_IPC;
    }

    /**
     * Check if request can be delivered.
     */
    public function canBeDelivered(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_IPC_HEAD;
    }

    /**
     * Check if request needs stock adjustment approval.
     */
    public function needsStockAdjustmentApproval(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if request needs GA Admin approval.
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
    
}
