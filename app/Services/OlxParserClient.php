<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class OlxParserClient implements OlxApiClientInterface
{
    private Client $http;
    private LoggerInterface $logger;

    public function __construct(Client $http, LoggerInterface $logger)
    {
        $this->http = $http;
        $this->logger = $logger;
    }

    public function fetchAdvert(string $url): ?array
    {
        try {
            $resp = $this->http->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html',
                ],
                'timeout' => 10,
            ]);

            if ($resp->getStatusCode() !== 200) {
                $this->logger->warning("OLX page returned {$resp->getStatusCode()} for URL: {$url}");
                return null;
            }

            $html = (string) $resp->getBody();
            $crawler = new Crawler($html);

            $jsonLdData = null;
            $crawler->filter('script[type="application/ld+json"]')
                ->each(function (Crawler $node) use (&$jsonLdData) {
                    try {
                        $json = json_decode($node->text(), true);
                        if (isset($json['@type']) && isset($json['offers'])) {
                            $jsonLdData = $json;
                        }
                    } catch (\Exception $e) {
                        $this->logger->warning('Invalid JSON-LD: ' . $e->getMessage());
                    }
                });

            if (!$jsonLdData || !isset($jsonLdData['offers']['price']) || !isset($jsonLdData['sku'])) {
                $this->logger->warning("Price or SKU not found in JSON-LD for URL: {$url}");
                return null;
            }

            return [
                'external_id' => (string) $jsonLdData['sku'],
                'price' => (float) $jsonLdData['offers']['price'],
                'currency' => $jsonLdData['offers']['priceCurrency'] ?? 'UAH',
            ];
        } catch (GuzzleException $e) {
            $this->logger->error('OLX parse request failed: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            $this->logger->error('OLX parse error: ' . $e->getMessage());
            return null;
        }
    }
}
