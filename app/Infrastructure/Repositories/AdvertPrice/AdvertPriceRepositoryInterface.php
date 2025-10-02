<?php

namespace App\Infrastructure\Repositories\AdvertPrice;

use Illuminate\Database\Eloquent\Collection;

interface AdvertPriceRepositoryInterface
{
    public function create(int $advertId, float $price, string $currency);
    public function findByAdvertId(int $advertId): Collection;
}
