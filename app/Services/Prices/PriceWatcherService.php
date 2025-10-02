<?php

namespace App\Services\Prices;

use App\Infrastructure\Repositories\Advert\AdvertRepositoryInterface;
use App\Jobs\NotifySubscribersJob;
use App\Models\Advert;
use App\Services\AdvertPrice\AdvertPriceService;
use App\Services\OlxApiClientInterface;
use Illuminate\Support\Facades\Cache;

readonly class PriceWatcherService
{
    public function __construct(
        private AdvertRepositoryInterface $adverts,
        private OlxApiClientInterface $olxClient,
        private AdvertPriceService $advertPriceService
    ) {
    }

    public function checkAdvert(Advert $model): void
    {
        $lockKey = "advert_check_{$model->id}";
        $lock = Cache::lock($lockKey, 300);
        if (!$lock->get()) {
            return;
        }

        try {
            $result = $this->olxClient->fetchAdvert($model->url);
            if ($result === null) {
                $model->check_error_count++;
                if ($model->check_error_count >= 10) {
                    $model->is_active = false;
                }
                $model->save();
                return;
            }

            if (!empty($result['external_id']) && empty($model->external_id)) {
                $this->adverts->saveExternalId($model->id, (string) $result['external_id']);
            }

            $newPrice = $result['price'];
            $currency = $result['currency'] ?? $model->currency;
            $oldPrice = $model->last_price;

            if ($oldPrice === null || (float) $oldPrice !== (float) $newPrice) {
                $this->adverts->updatePrice($model->id, $newPrice, $currency);
                NotifySubscribersJob::dispatch(
                    $model->id,
                    $oldPrice,
                    $newPrice,
                    $this->advertPriceService->getPriceHistory($model->id)
                );
            } else {
                $this->adverts->updatePrice($model->id, $oldPrice, $currency);
            }
        } finally {
            $lock->release();
        }
    }
}
