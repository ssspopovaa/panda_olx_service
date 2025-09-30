<?php

namespace App\Repositories;

use App\Models\Advert;

interface AdvertRepositoryInterface
{
    public function findByUrl(string $url): ?Advert;
    public function firstOrCreateByUrl(string $url): Advert;
    public function updatePrice(int $advertId, ?float $price, ?string $currency): void;
    public function saveExternalId(int $advertId, string $externalId): void;
    public function getPriceHistory(int $advertId): array;
}
