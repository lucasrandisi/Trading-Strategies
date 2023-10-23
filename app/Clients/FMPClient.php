<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;

class FMPClient {
    private const BASE_URL = 'https://financialmodelingprep.com/api';

    public static function request(string $endpoint) {
        $url = self::BASE_URL . $endpoint;

        return Http::get($url, [
            'apikey' => env('CRYPTO_COMPARE_API_KEY')
        ])->json();
    }
}
