<?php

namespace App\Application\Services\Subscription;

use App\Infrastructure\Repositories\Subscription\SubscriptionRepositoryInterface;

class SubscriptionService
{
    public function __construct(
        protected readonly SubscriptionRepositoryInterface $subscriptionRepository,
    )
    {
    }

    public function verifyByToken(string $token): bool
    {
        $subscription = $this->subscriptionRepository->findByToken($token);

        if (!$subscription) {
            throw new \RuntimeException('Invalid verification token');
        }

        $subscription->verified = true;
        $subscription->verified_at = now();
        $subscription->verification_token = null;

        return $this->subscriptionRepository->save($subscription);
    }
}
