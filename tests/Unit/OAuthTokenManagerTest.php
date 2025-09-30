<?php
namespace Tests\Unit;

use App\Services\OAuthTokenManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class OAuthTokenManagerTest extends TestCase
{
    public function test_get_access_token_caches_and_returns_token()
    {
        Cache::flush();
        $mockGuzzle = Mockery::mock(Client::class);
        $mockGuzzle->shouldReceive('post')->once()->andReturn(new Response(200, [], json_encode(['access_token' => 'abc', 'expires_in' => 3600])));

        $logger = $this->app['log'];
        $manager = new OAuthTokenManager($mockGuzzle, $logger);

        $token = $manager->getAccessToken();
        $this->assertEquals('abc', $token);

        $token2 = $manager->getAccessToken();
        $this->assertEquals('abc', $token2);
    }
}
