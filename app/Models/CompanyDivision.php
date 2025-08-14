<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyDivision extends Model
{
    protected $fillable = [
        'name',
        'initial',
    ];

    /**
     * Get the users in this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id');
    }

    /**
     * Get the division inventory settings.
     */
    public function inventorySettings(): HasMany
    {
        return $this->hasMany(DivisionInventorySetting::class, 'division_id');
    }

    /**
     * Get the stock requests from this division.
     */
    public function stockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'division_id');
    }

    /**
     * Get the current stock for this division.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockPerDivision::class, 'division_id');
    }

    /**
     * Get the print media stock movements for this division.
     */
    public function printMediaStockMovements(): HasMany
    {
        return $this->hasMany(PrintMediaStockMovement::class, 'division_id');
    }
}
