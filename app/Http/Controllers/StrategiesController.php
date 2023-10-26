<?php

namespace App\Http\Controllers;

use App\Http\Requests\Strategies\MovingAverageCrossoverRequest;
use App\Models\CryptoPrice;
use App\Strategies\MovingAverageCrossoverStrategy;

class StrategiesController extends Controller {
    public function movingAverageCrossover(MovingAverageCrossoverRequest $request) {
        $cryptoId = $request->input('crypto_id');
        $shortPeriod = $request->input('short_period');
        $longPeriod = $request->input('long_period');
        $initialUSD = $request->input('initial_usd');
        $initialCrypto = $request->input('initial_crypto');

        $cryptoPrices = CryptoPrice::where(['crypto_id' => $cryptoId])
            ->orderBy('date_time')
            ->get();

        return MovingAverageCrossoverStrategy::execute($cryptoPrices, $shortPeriod, $longPeriod, $initialUSD, $initialCrypto);
    }
}
