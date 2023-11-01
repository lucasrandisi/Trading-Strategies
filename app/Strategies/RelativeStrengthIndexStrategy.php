<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

class RelativeStrengthIndexStrategy extends InvestingStrategy {
    const RSI_BUY_THRESHOLD = 30;
    const RSI_SELL_THRESHOLD = 70;

    public function __construct(protected Collection $cryptoPrices, protected int $periods) {
    }

    protected function calculateSignals() {
        $signals = [];
        $priceChange = 0;
        $periodGain = 0;
        $periodLoss = 0;
        $accumulatedGains = 0;
        $accumulatedLosses = 0;
        $averageGain = 0;
        $averageLoss = 0;


        foreach ($this->cryptoPrices as $key => $cryptoPrice) {

            if ($key == 0) {
                $previousClosePrice = $cryptoPrice->close;
                continue;
            }

            $priceChange = $cryptoPrice->close - $previousClosePrice;

            if ($priceChange > 0) {
                $periodGain = $priceChange;
                $periodLoss = 0;
                $accumulatedGains += $periodGain;
            } else {
                $periodGain = 0;
                $periodLoss = abs($priceChange);
                $accumulatedLosses += $periodLoss;
            }

            $previousClosePrice = $cryptoPrice->close;


            if ($key + 1 < $this->periods) {
                continue;
            } else if ($key + 1 == $this->periods) {
                $averageGain = $accumulatedGains / $this->periods;
                $averageLoss = $accumulatedLosses / $this->periods;
            } else {
                $averageGain = ($averageGain * ($this->periods - 1) + $periodGain) / $this->periods;
                $averageLoss = ($averageLoss * ($this->periods - 1) + $periodLoss) / $this->periods;
            }

            $signals[$cryptoPrice->date_time]['average_gain'] = $averageGain;
            $signals[$cryptoPrice->date_time]['average_loss'] = $averageLoss;

            if ($averageLoss == 0) {
                $signals[$cryptoPrice->date_time]['rsi'] = 100;
            } else {
                $signals[$cryptoPrice->date_time]['rsi'] = round(100 - 100 / (1 + $averageGain / $averageLoss), 2);
            }

            if ($signals[$cryptoPrice->date_time]['rsi'] < static::RSI_BUY_THRESHOLD) {
                $signals[$cryptoPrice->date_time]['signal'] = static::BUY_SIGNAL;
            } else if ($signals[$cryptoPrice->date_time]['rsi'] > static::RSI_SELL_THRESHOLD) {
                $signals[$cryptoPrice->date_time]['signal'] = static::SELL_SIGNAL;
            } else {
                $signals[$cryptoPrice->date_time]['signal'] = static::HOLD_SIGNAL;
            }

            $signals[$cryptoPrice->date_time]['close'] = $cryptoPrice->close;
        }

        return $signals;
    }
}
