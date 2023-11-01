<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

class MovingAverageCrossoverStrategy extends InvestingStrategy {

    public function __construct(protected Collection $cryptoPrices, protected int $shortPeriod, protected int $longPeriod) {
    }

    protected function calculateSignals() {
        $averages = [];
        $previousShortPeriodAverage = null;
        $previousLongPeriodAverage = null;

        $shortPeriodSum = $this->cryptoPrices->slice(0, $this->shortPeriod)->sum('close');
        $longPeriodSum = $this->cryptoPrices->slice(0, $this->longPeriod)->sum('close');

        foreach ($this->cryptoPrices as $key => $cryptoPrice) {
            if ($key + 1 < $this->longPeriod) {
                continue;
            }

            // If $key + 1 == $longPeriod we have already calculated the $shortPeriodSum and $longPeriodSum. Otherwise, we update the sum by moving the window
            if ($key + 1 > $this->longPeriod) {
                $shortPeriodSum = $shortPeriodSum - $this->cryptoPrices[$key - $this->shortPeriod]->close + $cryptoPrice->close;
                $longPeriodSum = $longPeriodSum - $this->cryptoPrices[$key - $this->longPeriod]->close + $cryptoPrice->close;
            }

            $shortPeriodAverage = $shortPeriodSum / $this->shortPeriod;
            $longPeriodAverage = $longPeriodSum / $this->longPeriod;

            $averages[$cryptoPrice->date_time] = [
                'close' => $cryptoPrice->close,
                'short_period_average' => $shortPeriodAverage,
                'long_period_average' => $longPeriodAverage
            ];


            // At key + 1 == $longPeriod we cannot determine the signal because of the lack of the previous short and long averages
            if ($key + 1 == $this->longPeriod) {
                $previousShortPeriodAverage = $shortPeriodAverage;
                $previousLongPeriodAverage = $longPeriodAverage;
                continue;
            }

            if ($previousShortPeriodAverage <= $previousLongPeriodAverage && $shortPeriodAverage > $longPeriodAverage) {
                $averages[$cryptoPrice->date_time]['signal'] = self::BUY_SIGNAL;
            } elseif ($previousShortPeriodAverage >= $previousLongPeriodAverage && $shortPeriodAverage < $longPeriodAverage) {
                $averages[$cryptoPrice->date_time]['signal'] = self::SELL_SIGNAL;
            } else {
                $averages[$cryptoPrice->date_time]['signal'] = self::HOLD_SIGNAL;
            }

            $previousShortPeriodAverage = $shortPeriodAverage;
            $previousLongPeriodAverage = $longPeriodAverage;
        }

        return array_slice($averages, 1);
    }
}
