<?php

namespace App\Services\Subscription;

use App\Infrastructure\Repositories\Advert\AdvertRepositoryInterface;
use App\Infrastructure\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Mail\VerifySubscriptionMail;
use App\Services\Prices\PriceWatcherService;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionService
{
    private AdvertRepositoryInterface $adverts;
    private SubscriptionRepositoryInterface $subs;
    private PriceWatcherService $watcher;

    public function __construct(
        AdvertRepositoryInterface $adverts,
        SubscriptionRepositoryInterface $subs,
        PriceWatcherService $watcher
    ) {
        $this->adverts = $adverts;
        $this->subs = $subs;
        $this->watcher = $watcher;
    }

    public function createSubscriptionFromRequest(array $data): void
    {
        $advert = $this->adverts->firstOrCreateByUrl($data['url']);

        $this->watcher->checkAdvert($advert);

        $advert->refresh();
        if ($advert->last_price === null) {
            throw new Exception('Failed to parse price from URL: ' . $data['url']);
        }

        $subscription = $this->subs->firstOrCreate($advert->id, $data['email'], (string) Str::uuid());
        Mail::to($subscription->email)->queue(new VerifySubscriptionMail($subscription));
    }

    public function verifySubscriptionByToken(string $token): void
    {
        $this->subs->verifyByToken($token);
    }

    public function listSubscriptionsByEmail(string $email): array
    {
        $subscriptions = $this->subs->listByEmail($email);
        foreach ($subscriptions as &$sub) {
            $sub['price_history'] = $this->getPriceHistory($sub['advert_id']);
        }
        return $subscriptions;
    }

    public function getPriceHistory(int $advertId): array
    {
        return $this->adverts->getPriceHistory($advertId);
    }
}
