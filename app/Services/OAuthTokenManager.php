<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;

class OAuthTokenManager
{
    private Client $http;
    private LoggerInterface $logger;
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct(Client $http, LoggerInterface $logger)
    {
        $this->http = $http;
        $this->logger = $logger;
        $this->baseUrl = rtrim(config('services.olx.base_url'), '/');
        $this->clientId = config('services.olx.client_id');
        $this->clientSecret = config('services.olx.client_secret');
    }

    public function getAccessToken(): ?string
    {
        return Cache::remember('olx_access_token', 3500, function () {
            try {
                $resp = $this->http->post($this->baseUrl . '/api/open/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ],
                    'headers' => ['Accept' => 'application/json'],
                ]);

                $data = json_decode((string) $resp->getBody(), true);
                if (!isset($data['access_token'])) {
                    $this->logger->error('OLX token response missing access_token', $data);
                    return null;
                }
                $ttl = isset($data['expires_in']) ? max(60, $data['expires_in'] - 60) : 3500;
                Cache::put('olx_access_token', $data['access_token'], $ttl);
                return $data['access_token'];
            } catch (\Throwable $e) {
                $this->logger->error('Failed to refresh OLX token: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function refreshToken(): ?string
    {
        Cache::forget('olx_access_token');
        return $this->getAccessToken();
    }
}
