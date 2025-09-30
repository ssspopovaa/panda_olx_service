<?php

namespace App\Services;

interface OlxApiClientInterface
{
    public function fetchAdvert(string $url): ?array;
}
