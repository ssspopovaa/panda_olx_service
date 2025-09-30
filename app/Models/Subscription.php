<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['advert_id', 'email', 'verified', 'verification_token', 'verified_at'];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(Advert::class);
    }
}
