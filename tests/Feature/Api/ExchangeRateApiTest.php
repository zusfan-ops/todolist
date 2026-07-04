<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_the_rate(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates' => ['IDR' => 16000],
            ]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/exchange-rate/usd-idr')
            ->assertOk()
            ->assertJsonPath('data.rate', 16000);
    }

    public function test_guest_cannot_fetch_the_rate(): void
    {
        $this->getJson('/api/exchange-rate/usd-idr')->assertUnauthorized();
    }

    public function test_returns_503_when_upstream_unavailable(): void
    {
        Http::fake(['open.er-api.com/*' => Http::response([], 500)]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/exchange-rate/usd-idr')
            ->assertStatus(503);
    }
}
