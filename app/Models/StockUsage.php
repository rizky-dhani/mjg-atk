<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockUsage extends Model
{
    protected $guarded = ['id'];

    public function division()
    {
        return $this->belongsTo(CompanyDivision::class);
    }
    public function item()
    {
        return $this->belongsTo(OfficeStationeryItem::class);
    }
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }
        public function stock()
    {
    return $this->belongsTo(OfficeStationeryStockPerDivision::class);
    }
    public function items()
    {
        return $this->hasMany(StockUsageItem::class);
    }
    
}
