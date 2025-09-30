<?php

namespace App\Jobs;

use App\Models\Advert;
use App\Services\PriceWatcherServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAdvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $listingId)
    {
    }

    public function handle(PriceWatcherServiceInterface $service)
    {
        $advert = Advert::find($this->listingId);
        if ($advert) {
            $service->checkAdvert($advert);
        }
    }
}
