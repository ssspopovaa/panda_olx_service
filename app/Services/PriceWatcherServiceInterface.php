<?php

namespace App\Services;

use App\Models\Advert;

interface PriceWatcherServiceInterface
{
    public function checkAdvert(Advert $model): void;
}
