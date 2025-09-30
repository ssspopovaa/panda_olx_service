<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class OlxApiClient implements OlxApiClientInterface
{
    private Client $http;
    private OAuthTokenManager $tokenManager;
    private string $baseUrl;
    private LoggerInterface $logger;

    public function __construct(Client $http, OAuthTokenManager $tokenManager, LoggerInterface $logger)
    {
        $this->http = $http;
        $this->tokenManager = $tokenManager;
        $this->baseUrl = rtrim(config('services.olx.base_url'), '/');
        $this->logger = $logger;
    }

    public function fetchAdvert(string $url): ?array
    {
        $advertId = $this->extractAdvertIdFromUrl($url);

        if (!$advertId) {
            $this->logger->warning("Can't extract advert id from url: {$url}");
            return null;
        }

        $token = $this->tokenManager->getAccessToken();
        if (!$token) return null;

        try {
            $resp = $this->http->get("{$this->baseUrl}/api/partner/adverts/{$advertId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ],
                'timeout' => 10,
            ]);

            if ($resp->getStatusCode() !== 200) {
                $this->logger->warning("OLX adverts endpoint returned {$resp->getStatusCode()}");
                return null;
            }

            $data = json_decode((string) $resp->getBody(), true);
            if (!$data) return null;

            if (isset($data['id']) && isset($data['price']['amount'])) {
                return [
                    'external_id' => (string)$data['id'],
                    'price' => (float)$data['price']['amount'],
                    'currency' => $data['price']['currency'] ?? null,
                ];
            }

            if (isset($data['data'])) {
                $d = $data['data'];
                $id = $d['id'] ?? null;
                $price = $d['attributes']['price']['amount'] ?? ($d['attributes']['price'] ?? null);
                $currency = $d['attributes']['price']['currency'] ?? null;
                if ($price !== null) {
                    return [
                        'external_id' => $id,
                        'price' => (float)$price,
                        'currency' => $currency,
                    ];
                }
            }

            return null;
        } catch (GuzzleException $e) {
            if (
                method_exists($e, 'getResponse') &&
                $e->getResponse() &&
                $e->getResponse()->getStatusCode() === 401
            ) {
                $this->logger->info('OLX token may be expired - refreshing and retrying');
                $token = $this->tokenManager->refreshToken();
                if (!$token) return null;
                try {
                    $resp = $this->http->get("{$this->baseUrl}/api/partner/adverts/{$advertId}", [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept' => 'application/json'
                        ],
                        'timeout' => 10,
                    ]);
                    $data = json_decode((string) $resp->getBody(), true);
                    if (isset($data['id']) && isset($data['price']['amount'])) {
                        return [
                            'external_id' => (string)$data['id'],
                            'price' => (float)$data['price']['amount'],
                            'currency' => $data['price']['currency'] ?? null,
                        ];
                    }
                } catch (\Throwable $ex) {
                    $this->logger->error('Retry after refresh failed: ' . $ex->getMessage());
                    return null;
                }
            }

            $this->logger->error('OLX API request failed: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            $this->logger->error('OLX API error: ' . $e->getMessage());
            return null;
        }
    }

    private function extractAdvertIdFromUrl(string $url): ?string
    {
        // TODO after getting credentials from OLX API Developers need to implement this method

        return null;
    }
}
