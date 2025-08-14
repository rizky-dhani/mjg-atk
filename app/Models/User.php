<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'initial',
        'password',
        'division_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the division that the user belongs to.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(CompanyDivision::class);
    }

    /**
     * Get the stock requests submitted by this user.
     */
    public function stockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'requested_by');
    }

    /**
     * Get the stock requests approved by this user.
     */
    public function approvedStockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'approved_by');
    }

    /**
     * Get the stock requests delivered by this user (IPC Staff).
     */
    public function deliveredStockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'delivered_by');
    }
}
