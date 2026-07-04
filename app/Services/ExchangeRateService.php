<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const CACHE_KEY = 'exchange_rate_usd_idr';

    private const ENDPOINT = 'https://open.er-api.com/v6/latest/USD';

    /**
     * USD→IDR rate, cached for a few hours — the free upstream API only
     * refreshes once a day anyway, so there's no point hitting it more often.
     *
     * @return array{rate: float, updated_at: string}|null
     */
    public function usdToIdr(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached && isset($cached['expires_at']) && now()->lt($cached['expires_at'])) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get(self::ENDPOINT);
            $rate = $response->ok() && $response->json('result') === 'success'
                ? $response->json('rates.IDR')
                : null;

            if (! $rate) {
                throw new \RuntimeException('Upstream rate unavailable');
            }

            $fresh = [
                'rate' => (float) $rate,
                'updated_at' => now()->toIso8601String(),
                'expires_at' => now()->addHours(6),
            ];

            Cache::put(self::CACHE_KEY, $fresh, now()->addDays(2));

            return $fresh;
        } catch (\Throwable $e) {
            Log::warning('ExchangeRateService: failed to fetch USD/IDR rate', ['error' => $e->getMessage()]);

            // Serve stale data rather than nothing if we have it — a rate
            // from a few hours ago is still far more useful than none.
            return $cached;
        }
    }
}
