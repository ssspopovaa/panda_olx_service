<?php

namespace App\Infrastructure\Repositories\Advert;

use App\Models\Advert;
use App\Models\AdvertPrice;

class AdvertRepository implements AdvertRepositoryInterface
{
    public function findById(int $id): ?Advert
    {
        return Advert::find($id);
    }

    public function findByUrl(string $url): ?Advert
    {
        return Advert::where('url', $url)->first();
    }

    public function firstOrCreateByUrl(string $url): Advert
    {
        return Advert::firstOrCreate(['url' => $url]);
    }

    public function updatePrice(int $advertId, ?float $price, ?string $currency): void
    {
        $advert = Advert::find($advertId);
        if (!$advert) {
            return;
        }

        if ($price !== null) {
            AdvertPrice::create([
                'advert_id' => $advertId,
                'price' => $price,
                'currency' => $currency,
            ]);
        }

        $advert->last_price = $price;
        $advert->currency = $currency;
        $advert->last_checked_at = now();
        $advert->check_error_count = 0;
        $advert->save();
    }

    public function saveExternalId(int $advertId, string $externalId): void
    {
        $advert = Advert::find($advertId);
        if (!$advert) return;
        $advert->external_id = $externalId;
        $advert->save();
    }

    public function getPriceHistory(int $advertId): array
    {
        return AdvertPrice::where('advert_id', $advertId)
            ->orderBy('changed_at', 'desc')
            ->get(['price', 'currency', 'changed_at'])
            ->toArray();
    }
}
