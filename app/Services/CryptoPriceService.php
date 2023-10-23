<?php

namespace App\Services;

use App\Models\CryptoPrice;

class CryptoPriceService {
    public static function store(int $cryptoId, string $dateTime, float $open, float $close, float $low, float $high, int $volume) {
        $cryptoPrice = new CryptoPrice();
        $cryptoPrice->fill([
            'crypto_id' => $cryptoId,
            'date_time' => $dateTime,
            'open' => $open,
            'close' => $close,
            'low' => $low,
            'high' => $high,
            'volume' => $volume
        ]);

        $cryptoPrice->save();
        return $cryptoPrice;
    }

}
