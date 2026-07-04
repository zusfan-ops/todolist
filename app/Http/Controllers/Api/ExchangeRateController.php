<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;

class ExchangeRateController extends Controller
{
    public function usdIdr(ExchangeRateService $rates)
    {
        $data = $rates->usdToIdr();

        if (! $data) {
            return response()->json(['message' => 'Kurs sedang tidak tersedia'], 503);
        }

        return response()->json([
            'data' => [
                'rate' => $data['rate'],
                'updated_at' => $data['updated_at'],
            ],
        ]);
    }
}
