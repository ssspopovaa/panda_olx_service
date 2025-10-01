<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AdvertPriceRepositoryInterface;
use App\Repositories\AdvertPricePriceRepository;
use App\Repositories\SubscriptionRepositoryInterface;
use App\Repositories\SubscriptionRepository;
use App\Services\SubscriptionServiceInterface;
use App\Services\SubscriptionService;
use App\Services\OlxApiClientInterface;
use App\Services\OlxApiClient;
use App\Services\OlxParserClient;
use App\Services\OAuthTokenManager;
use App\Services\PriceWatcherServiceInterface;
use App\Services\PriceWatcherService;
use GuzzleHttp\Client;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AdvertPriceRepositoryInterface::class, AdvertPricePriceRepository::class);
        $this->app->singleton(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);

        $this->app->singleton(SubscriptionServiceInterface::class, function ($app) {
            return new SubscriptionService(
                $app->make(AdvertPriceRepositoryInterface::class),
                $app->make(SubscriptionRepositoryInterface::class),
                $app->make(PriceWatcherServiceInterface::class)
            );
        });

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

        $this->app->singleton(PriceWatcherServiceInterface::class, function ($app) {
            return new PriceWatcherService(
                $app->make(AdvertPriceRepositoryInterface::class),
                $app->make(OlxApiClientInterface::class)
            );
        });
    }

    public function boot()
    {
    }
}
