<?php

namespace App\Services\AdvertPrice;

use App\Infrastructure\Repositories\Advert\AdvertRepositoryInterface;
use App\Infrastructure\Repositories\AdvertPrice\AdvertPriceRepositoryInterface;

readonly class AdvertPriceService
{
    public function __construct(
        protected AdvertPriceRepositoryInterface $priceRepository,
        protected AdvertRepositoryInterface $advertRepository,
    ) {
    }

    public function getPriceHistory(int $advertId): array
    {
        $prices = $this->priceRepository->findByAdvertId($advertId);

        return $prices
            ->sortByDesc('changed_at')
            ->map(fn($price) => [
                'price' => $price->price,
                'currency' => $price->currency,
                'changed_at' => $price->changed_at,
            ])
            ->toArray();
    }

//    public function updatePrice(int $advertId, ?float $price, ?string $currency): bool
//    {
//        $advert = $this->advertRepository->find($advertId);
//
//        if (!$advert) {
//            throw new \RuntimeException("Advert not found: $advertId");
//        }
//
//        if ($price !== null) {
//            $this->priceRepository->create($advertId, $price, $currency);
//        }
//
//        $advert->last_price = $price;
//        $advert->currency = $currency;
//        $advert->last_checked_at = now();
//        $advert->check_error_count = 0;
//
//        return $this->advertRepository->save($advert);
//    }
}
