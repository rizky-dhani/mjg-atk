<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class MarketingMediaStockRequest
 *
 * @property int $id
 * @property string $request_number
 * @property int $division_id
 * @property int $requested_by
 * @property string $type
 * @property string $status
 * @property string $notes
 * @property string $rejection_reason
 * @property int $approval_head_id
 * @property \Illuminate\Support\Carbon $approval_head_at
 * @property int $rejection_head_id
 * @property \Illuminate\Support\Carbon $rejection_head_at
 * @property int $approval_admin_ga_id
 * @property \Illuminate\Support\Carbon $approval_admin_ga_at
 * @property int $rejection_admin_ga_id
 * @property \Illuminate\Support\Carbon $rejection_admin_ga_at
 * @property int $approval_mkt_head_id
 * @property \Illuminate\Support\Carbon $approval_mkt_head_at
 * @property int $rejection_mkt_head_id
 * @property \Illuminate\Support\Carbon $rejection_mkt_head_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read CompanyDivision $division
 * @property-read User $requester
 * @property-read User $divisionHead
 * @property-read User $rejectionHead
 * @property-read User $gaAdmin
 * @property-read User $rejectionGaAdmin
 * @property-read User $marketingSupportHead
 * @property-read User $rejectionMarketingSupportHead
 * @property-read MarketingMediaStockRequestItem[] $items
 */
class MarketingMediaStockRequest extends Model
{
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
        'approval_head_at' => 'datetime',
        'rejection_head_at' => 'datetime',
        'approval_admin_ga_at' => 'datetime',
        'rejection_admin_ga_at' => 'datetime',
        'approval_mkt_head_at' => 'datetime',
        'rejection_mkt_head_at' => 'datetime',
    ];

    const TYPE_INCREASE = 'increase';

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
            if (empty($model->request_number)) {
                // Get the division initial
                $division = CompanyDivision::find($model->division_id);
                $divisionInitial = $division ? $division->initial : 'DIV';
                
                // Get the latest request by request_number for this division to maintain proper sequence
                $latestRequest = MarketingMediaStockRequest::whereNotNull('request_number')
                    ->where('division_id', $model->division_id)
                    ->orderBy('request_number', 'desc')
                    ->first();
                
                if ($latestRequest) {
                    // Extract the numeric part from the latest request number and increment it
                    // Format is DIV-REQ-00000001, so we need to extract the numeric part after the last dash
                    $parts = explode('-', $latestRequest->request_number);
                    $latestNumber = intval(end($parts));
                    $nextNumber = $latestNumber + 1;
                } else {
                    // If no previous requests for this division, start with 1
                    $nextNumber = 1;
                }
                
                $model->request_number = $divisionInitial . '-MM-REQ-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
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
     * Get the items in this request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MarketingMediaStockRequestItem::class, 'stock_request_id');
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
     * Process stock adjustment for all items in this request.
     * This method should be called when a request is approved by all required parties.
     * This model is only for adding new stock (increase requests).
     * It updates the stock in MarketingMediaStockPerDivision table.
     */
    public function processStockAdjustment(): void
    {
        if (!$this->canProcessAdjustment()) {
            throw new \Exception('Cannot process stock adjustment for this request. Not fully approved.');
        }

        foreach ($this->items as $item) {
            // Get or create the stock record for this media in this division
            $stock = MarketingMediaStockPerDivision::firstOrCreate([
                'marketing_media_id' => $item->marketing_media_id,
                'division_id' => $this->division_id
            ], [
                'current_stock' => 0
            ]);
            
            // Store previous stock level for reference
            $item->previous_stock = $stock->current_stock;
            
            // Increase the stock by the requested quantity
            $stock->current_stock += $item->quantity;
            
            // Save the new stock level
            $stock->save();
            
            // Store new stock level for reference
            $item->new_stock = $stock->current_stock;
            $item->save();
        }
        
        // Update request status to completed
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Check if request can be processed (stock adjusted).
     */
    public function canProcessAdjustment(): bool
    {
        return $this->status === self::STATUS_APPROVED_BY_MKT_HEAD;
    }
}
