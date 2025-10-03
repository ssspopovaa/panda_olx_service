<?php

namespace App\Infrastructure\Repositories\AdvertPrice;

use App\Models\AdvertPrice;
use Illuminate\Database\Eloquent\Collection;

class AdvertPriceRepository implements AdvertPriceRepositoryInterface
{
    public function create(int $advertId, float $price, ?string $currency): AdvertPrice
    {
        return AdvertPrice::create([
            'advert_id' => $advertId,
            'price' => $price,
            'currency' => $currency,
        ]);
    }

    public function findByAdvertId(int $advertId): Collection
    {
        return AdvertPrice::where('advert_id', $advertId)->get();
    }
}
