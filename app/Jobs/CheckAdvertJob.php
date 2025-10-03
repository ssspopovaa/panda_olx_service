<?php

namespace App\Jobs;

use App\Infrastructure\Repositories\Advert\AdvertRepositoryInterface;
use App\Services\Prices\PriceWatcherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAdvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $advertId)
    {
    }

    public function handle(PriceWatcherService $service, AdvertRepositoryInterface $advertRepo)
    {
        $advert = $advertRepo->findById($this->advertId);

        if ($advert) {
            $service->checkAdvert($advert);
        }
    }
}
