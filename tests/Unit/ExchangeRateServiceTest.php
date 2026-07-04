<?php

namespace Tests\Unit;

use App\Services\ExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetches_and_caches_the_rate(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates' => ['IDR' => 15800.5],
            ]),
        ]);

        $rate = app(ExchangeRateService::class)->usdToIdr();

        $this->assertEquals(15800.5, $rate['rate']);
        Http::assertSentCount(1);

        // second call within the cache window must not hit the API again
        app(ExchangeRateService::class)->usdToIdr();
        Http::assertSentCount(1);
    }

    public function test_returns_null_when_upstream_fails_and_nothing_is_cached(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([], 500),
        ]);

        $rate = app(ExchangeRateService::class)->usdToIdr();

        $this->assertNull($rate);
    }

    public function test_serves_stale_cache_when_upstream_fails(): void
    {
        Cache::put('exchange_rate_usd_idr', [
            'rate' => 15000.0,
            'updated_at' => now()->subDay()->toIso8601String(),
            'expires_at' => now()->subHour(),
        ], now()->addDays(2));

        Http::fake([
            'open.er-api.com/*' => Http::response([], 500),
        ]);

        $rate = app(ExchangeRateService::class)->usdToIdr();

        $this->assertEquals(15000.0, $rate['rate']);
    }
}
