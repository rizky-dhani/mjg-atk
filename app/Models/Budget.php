<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = [
        'division_id',
        'initial_amount',
        'current_amount',
        'type',
        'notes',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];

    /**
     * Get the division that owns this budget.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Scope to get ATK budgets only.
     */
    public function scopeAtk($query)
    {
        return $query->where('type', 'ATK');
    }

    /**
     * Scope to get Marketing Media budgets only.
     */
    public function scopeMarketingMedia($query)
    {
        return $query->where('type', 'Marketing Media');
    }

    /**
     * Deduct an amount from the current budget.
     */
    public function deductAmount($amount)
    {
        $this->current_amount -= $amount;
        $this->save();
    }

    /**
     * Add an amount to the current budget.
     */
    public function addAmount($amount): void
    {
        $this->current_amount += $amount;
        $this->save();
    }

    /**
     * Check if the budget has sufficient funds for a given amount.
     */
    public function hasSufficientFunds($amount): bool
    {
        return $this->current_amount >= $amount;
    }

    /**
     * Get the remaining budget amount.
     */
    public function getRemainingAmount()
    {
        return $this->current_amount;
    }

    /**
     * Get the used budget amount.
     */
    public function getUsedAmount()
    {
        return $this->initial_amount - $this->current_amount;
    }

    /**
     * Get the percentage of budget used.
     */
    public function getUsedPercentage(): float
    {
        if ($this->initial_amount == 0) {
            return 0;
        }
        return ($this->getUsedAmount() / $this->initial_amount) * 100;
    }
}