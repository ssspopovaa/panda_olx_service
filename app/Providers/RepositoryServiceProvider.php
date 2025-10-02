<?php

namespace App\Providers;

use App\Infrastructure\Repositories\Advert\AdvertRepository;
use App\Infrastructure\Repositories\Advert\AdvertRepositoryInterface;
use App\Infrastructure\Repositories\AdvertPrice\AdvertPriceRepository;
use App\Infrastructure\Repositories\AdvertPrice\AdvertPriceRepositoryInterface;
use App\Infrastructure\Repositories\Subscription\SubscriptionRepository;
use App\Infrastructure\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Services\OAuthTokenManager;
use App\Services\OlxApiClient;
use App\Services\OlxApiClientInterface;
use App\Services\OlxParserClient;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AdvertRepositoryInterface::class, AdvertRepository::class);
        $this->app->singleton(AdvertPriceRepositoryInterface::class, AdvertPriceRepository::class);
        $this->app->singleton(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);

        $this->app->singleton(OlxApiClientInterface::class, function ($app) {
            $type = config('services.olx.client_type', 'parser');
            $http = new Client(['timeout' => 10]);
            $logger = $app['log'];

            if ($type === 'api') {
                if (empty(config('services.olx.client_id')) || empty(config('services.olx.client_secret'))) {
                    throw new \Exception('OLX API credentials are missing in config');
                }
                $tokenManager = new OAuthTokenManager($http, $logger);
                return new OlxApiClient($http, $tokenManager, $logger);
            } elseif ($type === 'parser') {
                return new OlxParserClient($http, $logger);
            } else {
                throw new \Exception("Unknown OLX client type: {$type}");
            }
        });
    }

    public function boot()
    {
    }
}
