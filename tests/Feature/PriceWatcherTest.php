<?php

namespace Tests\Feature;

use App\Jobs\CheckAdvertJob;
use App\Jobs\NotifySubscribersJob;
use App\Models\Advert;
use App\Services\OlxApiClientInterface;
use App\Services\Prices\PriceWatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PriceWatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_change_saves_to_history_and_triggers_notification()
    {
        Queue::fake();

        $advert = Advert::create([
            'url' => 'https://olx.ua/test',
            'last_price' => 1000.0,
            'currency' => 'UAH',
            'check_error_count' => 0,
            'is_active' => true,
        ]);

        $mockClient = Mockery::mock(OlxApiClientInterface::class);
        $mockClient->shouldReceive('fetchAdvert')
            ->with('https://olx.ua/test')
            ->once()
            ->andReturn([
                'external_id' => '901013040',
                'price' => 1200.0,
                'currency' => 'UAH',
            ]);

        $this->app->instance(OlxApiClientInterface::class, $mockClient);

        $service = app(PriceWatcherService::class);
        $service->checkAdvert($advert);

        $advert->refresh();

        $this->assertEquals(1200.0, (float) $advert->last_price, 'last_price did not update to 1200.0');

        $this->assertDatabaseHas('advert_prices', [
            'advert_id' => $advert->id,
            'price' => '1200.00',
            'currency' => 'UAH',
        ]);

        Queue::assertPushed(NotifySubscribersJob::class);
    }

    public function test_multiple_price_changes_saved_to_history()
    {
        Queue::fake();

        $advert = Advert::create([
            'url' => 'https://olx.ua/test',
            'last_price' => 1000.0,
            'currency' => 'UAH',
            'check_error_count' => 0,
            'is_active' => true,
        ]);

        $mockClient = Mockery::mock(OlxApiClientInterface::class);
        $mockClient->shouldReceive('fetchAdvert')
            ->with('https://olx.ua/test')
            ->times(2)
            ->andReturn(
                ['external_id' => '901013040', 'price' => 1200.0, 'currency' => 'UAH'],
                ['external_id' => '901013040', 'price' => 1100.0, 'currency' => 'UAH']
            );

        $this->app->instance(OlxApiClientInterface::class, $mockClient);

        $service = app(PriceWatcherService::class);

        $service->checkAdvert($advert);
        $advert->refresh();
        $this->assertEqualsWithDelta(1200.0, (float) $advert->last_price, 0.001);

        $service->checkAdvert($advert);
        $advert->refresh();
        $this->assertEqualsWithDelta(1100.0, (float) $advert->last_price, 0.001);

        $this->assertDatabaseHas('advert_prices', [
            'advert_id' => $advert->id,
            'price' => '1200.00',
        ]);

        $this->assertDatabaseHas('advert_prices', [
            'advert_id' => $advert->id,
            'price' => '1100.00',
        ]);

        Queue::assertPushed(NotifySubscribersJob::class, 2);
    }

    public function test_advert_deactivated_after_multiple_errors()
    {
        Queue::fake();

        $advert = Advert::create([
            'url' => 'https://olx.ua/test-error',
            'last_price' => 1000.0,
            'currency' => 'UAH',
            'check_error_count' => 9,
            'is_active' => true,
        ]);

        $mockClient = Mockery::mock(OlxApiClientInterface::class);
        $mockClient->shouldReceive('fetchAdvert')
            ->with('https://olx.ua/test-error')
            ->once()
            ->andReturn(null);

        $this->app->instance(OlxApiClientInterface::class, $mockClient);

        $service = app(PriceWatcherService::class);
        $service->checkAdvert($advert);

        $advert->refresh();
        $this->assertFalse($advert->is_active);
        $this->assertEquals(10, $advert->check_error_count);
    }

    public function test_job_dispatches_correctly()
    {
        Queue::fake();

        $advert = Advert::create([
            'url' => 'https://olx.ua/test',
            'last_price' => 1000.0,
            'currency' => 'UAH',
            'check_error_count' => 0,
            'is_active' => true,
        ]);

        CheckAdvertJob::dispatch($advert->id);

        Queue::assertPushed(CheckAdvertJob::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
