<?php

namespace App\Infrastructure\Repositories\Subscription;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    public function firstOrCreate(int $advertId, string $email, string $verificationToken): Subscription;
    public function verifyByToken(string $token): void;
    public function getVerifiedEmailsByAdvertId(int $advertId): array;
    public function listByEmail(string $email): array;
}
