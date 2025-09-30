<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertPrice extends Model
{
    protected $fillable = ['advert_id', 'price', 'currency', 'changed_at'];

    protected $casts = [
        'price' => 'float',
        'changed_at' => 'datetime',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(Advert::class);
    }
}
