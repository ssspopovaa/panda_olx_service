<?php

namespace App\Infrastructure\Repositories\Advert;

use App\Models\Advert;

interface AdvertRepositoryInterface
{
    public function find(int $id): ?Advert;
    public function findByUrl(string $url): ?Advert;
    public function firstOrCreateByUrl(string $url): Advert;
    public function updatePrice(int $advertId, ?float $price, ?string $currency): void;
    public function saveExternalId(int $advertId, string $externalId): void;
}
