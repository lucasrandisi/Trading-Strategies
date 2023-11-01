<?php

namespace App\Http\Controllers;

use App\Http\Requests\Strategies\MovingAverageCrossoverRequest;
use App\Http\Requests\Strategies\RelativeStrengthIndexRequest;
use App\Models\CryptoPrice;
use App\Strategies\MovingAverageCrossoverStrategy;
use App\Strategies\RelativeStrengthIndexStrategy;

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

        return (new MovingAverageCrossoverStrategy($cryptoPrices, $shortPeriod, $longPeriod))->executeTradings($initialUSD, $initialCrypto);
    }


    public function relativeStrengthIndex(RelativeStrengthIndexRequest $request) {
        $cryptoId = $request->input('crypto_id');
        $periods = $request->input('periods');
        $initialUSD = $request->input('initial_usd');
        $initialCrypto = $request->input('initial_crypto');

        $cryptoPrices = CryptoPrice::where(['crypto_id' => $cryptoId])
            ->orderBy('date_time')
            ->get();

        return (new RelativeStrengthIndexStrategy($cryptoPrices, $periods))->executeTradings($initialUSD, $initialCrypto);
    }
}
