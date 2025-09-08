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
     * Get the office stationery division inventory settings.
     */
    public function officeStationeryInventorySettings(): HasMany
    {
        return $this->hasMany(OfficeStationeryDivisionInventorySetting::class, 'division_id');
    }

    /**
     * Get the marketing media division inventory settings.
     */
    public function marketingMediaInventorySettings(): HasMany
    {
        return $this->hasMany(MarketingMediaDivisionInventorySetting::class, 'division_id');
    }

    /**
     * Get the stock requests from this division.
     */
    public function stockRequests(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockRequest::class, 'division_id');
    }

    /**
     * Get the current stock for this division.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(OfficeStationeryStockPerDivision::class, 'division_id');
    }

    /**
     * Get the marketing media stock requests for this division.
     */
    public function marketingMediaStockRequests(): HasMany
    {
        return $this->hasMany(MarketingMediaStockRequest::class, 'division_id');
    }
}
