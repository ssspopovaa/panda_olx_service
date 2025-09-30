<?php

namespace App\Services;

interface SubscriptionServiceInterface
{
    public function createSubscriptionFromRequest(array $data): void;
    public function verifySubscriptionByToken(string $token): void;
    public function listSubscriptionsByEmail(string $email): array;
    public function getPriceHistory(int $advertId): array;
}
