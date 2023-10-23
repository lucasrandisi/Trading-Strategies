<?php

namespace App\Services;

use App\Models\Crypto;

class CryptoService {
    public static function store(string $name) {
        $crypto = new Crypto();
        $crypto->name = $name;
        $crypto->save();

        return $crypto;
    }
}
