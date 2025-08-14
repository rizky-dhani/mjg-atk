<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PrintMediaCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Get the print media items that belong to this category.
     */
    public function printMediaItems(): HasMany
    {
        return $this->hasMany(PrintMedia::class, 'category_id');
    }
}
