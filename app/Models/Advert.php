<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advert extends Model
{
    protected $fillable = [
        'url',
        'external_id',
        'last_price',
        'currency',
        'last_checked_at',
        'check_error_count',
        'is_active'
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'last_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(AdvertPrice::class);
    }
}
