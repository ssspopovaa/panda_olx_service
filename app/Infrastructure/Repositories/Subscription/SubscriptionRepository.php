<?php

namespace App\Infrastructure\Repositories\Subscription;

use App\Models\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function firstOrCreate(int $advertId, string $email, string $verificationToken): Subscription
    {
        return Subscription::firstOrCreate(
            ['advert_id' => $advertId, 'email' => $email],
            ['verification_token' => $verificationToken, 'verified' => false]
        );
    }

    public function verifyByToken(string $token): void
    {
        $sub = Subscription::where('verification_token', $token)->firstOrFail();
        $sub->verified = true;
        $sub->verified_at = now();
        $sub->verification_token = null;
        $sub->save();
    }

    public function getVerifiedEmailsByAdvertId(int $advertId): array
    {
        return Subscription::where('advert_id', $advertId)->where('verified', true)->pluck('email')->toArray();
    }

    public function listByEmail(string $email): array
    {
        return Subscription::where('email', $email)->with('advert')->get()->toArray();
    }
}
