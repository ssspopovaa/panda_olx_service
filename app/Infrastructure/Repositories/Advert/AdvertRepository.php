<?php

namespace App\Infrastructure\Repositories\Advert;

use App\Models\Advert;
use App\Models\AdvertPrice;

class AdvertRepository implements AdvertRepositoryInterface
{
    public function find(int $id): ?Advert
    {
        return Advert::findOrFail($id);
    }

    public function findByUrl(string $url): ?Advert
    {
        return Advert::where('url', $url)->first();
    }

    public function firstOrCreateByUrl(string $url): Advert
    {
        return Advert::firstOrCreate(['url' => $url]);
    }

    public function saveExternalId(int $advertId, string $externalId): void
    {
        $advert = Advert::find($advertId);
        if (!$advert) return;
        $advert->external_id = $externalId;
        $advert->save();
    }

    public function save(Advert $advert): bool
    {
        return $advert->save();
    }
}
