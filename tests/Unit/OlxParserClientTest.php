<?php
namespace Tests\Unit;

use App\Services\OlxParserClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class OlxParserClientTest extends TestCase
{
    public function test_fetch_listing_parses_price_from_json_ld()
    {
        $mockHtml = <<<HTML
<html>
<head>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Vehicle",
            "sku": "901013040",
            "offers": {
                "@type": "Offer",
                "price": 3700,
                "priceCurrency": "USD"
            }
        }
    </script>
</head>
</html>
HTML;

        $mockGuzzle = Mockery::mock(Client::class);
        $mockGuzzle->shouldReceive('get')->once()->andReturn(new Response(200, [], $mockHtml));

        $logger = $this->app['log'];
        $parser = new OlxParserClient($mockGuzzle, $logger);

        $result = $parser->fetchAdvert('https://www.olx.ua/d/uk/obyavlenie/prodam-sanatu-gaz-benzin-horoshaya-IDYYyEo.html');

        $this->assertEquals('901013040', $result['external_id']);
        $this->assertEquals(3700.0, $result['price']);
        $this->assertEquals('USD', $result['currency']);
    }

    public function test_fetch_listing_returns_null_on_404()
    {
        $mockGuzzle = Mockery::mock(Client::class);
        $mockGuzzle->shouldReceive('get')->once()->andReturn(new Response(404, [], ''));

        $logger = $this->app['log'];
        $parser = new OlxParserClient($mockGuzzle, $logger);

        $result = $parser->fetchAdvert('https://www.olx.ua/d/uk/obyavlenie/invalid-ID123456.html');

        $this->assertNull($result);
    }

    public function test_fetch_listing_returns_null_on_invalid_json_ld()
    {
        $mockHtml = <<<HTML
<html>
<head>
    <script type="application/ld+json">{}</script>
</head>
</html>
HTML;

        $mockGuzzle = Mockery::mock(Client::class);
        $mockGuzzle->shouldReceive('get')->once()->andReturn(new Response(200, [], $mockHtml));

        $logger = $this->app['log'];
        $parser = new OlxParserClient($mockGuzzle, $logger);

        $result = $parser->fetchAdvert('https://www.olx.ua/d/uk/obyavlenie/invalid-ID123456.html');

        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
