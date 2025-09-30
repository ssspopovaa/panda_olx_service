<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\Subscription;
use App\Models\AdvertPrice;
use App\Services\OlxApiClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifySubscriptionMail;
use Mockery;
use Tests\TestCase;

class SubscribeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_endpoint_creates_subscription_and_sends_mail()
    {
        Mail::fake();

        $mockClient = Mockery::mock(OlxApiClientInterface::class);
        $mockClient->shouldReceive('fetchAdvert')
            ->once()
            ->andReturn(['external_id' => '123', 'price' => 1000.0, 'currency' => 'UAH']);
        $this->app->instance(OlxApiClientInterface::class, $mockClient);

        $res = $this->postJson('/api/subscribe', [
            'url' => 'https://olx.ua/abc-123456',
            'email' => 'a@b.com'
        ]);

        $res->assertStatus(202);

        $this->assertDatabaseHas('adverts', [
            'url' => 'https://olx.ua/abc-123456',
            'last_price' => '1000.00',
        ]);
        $this->assertDatabaseHas('subscriptions', ['email' => 'a@b.com']);

        Mail::assertQueued(VerifySubscriptionMail::class);
    }

    public function test_verify_endpoint_marks_subscription_verified()
    {
        $advert = Advert::create([
            'url' => 'https://olx.ua/abc-123456',
            'last_price' => 1000.0,
            'currency' => 'UAH'
        ]);

        $subscription = Subscription::create([
            'advert_id' => $advert->id,
            'email' => 'a@b.com',
            'verification_token' => 'test-token',
            'verified' => false,
        ]);

        $res = $this->getJson('/api/verify?token=test-token');

        $res->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'email' => 'a@b.com',
            'verified' => true,
        ]);
    }

    public function test_list_subscriptions_includes_price_history()
    {
        $advert = Advert::create([
            'url' => 'https://olx.ua/abc-123456',
            'last_price' => 1000.0,
            'currency' => 'UAH'
        ]);

        $subscription = Subscription::create([
            'advert_id' => $advert->id,
            'email' => 'a@b.com',
            'verification_token' => 'test-token',
            'verified' => true,
        ]);

        AdvertPrice::create([
            'advert_id' => $advert->id,
            'price' => 1000.0,
            'currency' => 'UAH',
        ]);

        $res = $this->getJson('/api/subscriptions?email=a@b.com');
        $res->assertStatus(200);

        $res->assertJsonFragment(['price' => 1000.0]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
