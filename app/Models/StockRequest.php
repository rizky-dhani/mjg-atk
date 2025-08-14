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
        'approval_ipc_id',
        'approval_ipc_at',
        'delivered_by',
        'delivered_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const TYPE_INCREASE = 'increase';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_HEAD = 'approved_by_head';
    const STATUS_REJECTED_BY_HEAD = 'rejected_by_head';
    const STATUS_APPROVED_BY_IPC = 'approved_by_ipc';
    const STATUS_REJECTED_BY_IPC = 'rejected_by_ipc';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = 'REQ-' . date('Ymd') . '-' . str_pad(StockRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
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
        return $this->belongsTo(User::class, 'division_head_id');
    }

    /**
     * Get the user who approved this.
     */
    public function ipcStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_ipc_id');
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
     * Check if request can be delivered.
     */
    public function canBeDelivered(): bool
    {
        return $this->isIncrease() && $this->status === self::STATUS_APPROVED_BY_IPC;
    }
    
}
