<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockUsageItem extends Model
{
    protected $guarded = ['id'];

    public function usage()
    {
        return $this->belongsTo(StockUsage::class);
    }
    public function item()
    {
        return $this->belongsTo(OfficeStationeryItem::class, 'item_id');
    }
}
