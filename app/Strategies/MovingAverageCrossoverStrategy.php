<?php

namespace App\Strategies;

use App\Models\CryptoPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MovingAverageCrossoverStrategy {
    const BUY_SIGNAL = 'BUY';
    const SELL_SIGNAL = 'SELL';
    const HOLD_SIGNAL = 'HOLD';


    /**
     * @param Collection<CryptoPrice> $cryptoPrices
     * @param int $shortPeriod
     * @param int $longPeriod
     * @param int $initialUSD
     * @param int $initialCrypto
     */
    public static function execute(Collection $cryptoPrices, int $shortPeriod, int $longPeriod, float $initialUSD, float $initialCrypto) {
        $averages = static::calculateMovingAverages($cryptoPrices, $shortPeriod, $longPeriod);

        $tradings = [];
        $previousUSD = $initialUSD;
        $previousCrypto = $initialCrypto;

        foreach ($averages as $dateTime => $average) {
            if ($average['signal'] === self::HOLD_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => $previousUSD,
                    'crypto' => $previousCrypto
                ];
            } else if ($average['signal'] === self::BUY_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => 0,
                    'crypto' => $previousCrypto + $previousUSD / $average['close']
                ];
            } else if ($average['signal'] === self::SELL_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => $previousUSD + $previousCrypto * $average['close'],
                    'crypto' => 0
                ];
            }

            $tradings[$dateTime]['signal'] = $average['signal'];
            $tradings[$dateTime]['close'] = $average['close'];
            $tradings[$dateTime]['total_value'] = $tradings[$dateTime]['usd'] + $tradings[$dateTime]['crypto'] * $average['close'];

            $previousUSD = $tradings[$dateTime]['usd'];
            $previousCrypto = $tradings[$dateTime]['crypto'];
        }

        return $tradings;
    }

    /**
     * @param Collection<CryptoPrice> $cryptoPrices
     * @param int $shortPeriod
     * @param int $longPeriod
     */
    private static function calculateMovingAverages(Collection $cryptoPrices, int $shortPeriod, int $longPeriod) {
        return Cache::remember("{$cryptoPrices->first()->crypto_id}-$shortPeriod-$longPeriod", 3600, function () use ($cryptoPrices, $shortPeriod, $longPeriod) {
            $averages = [];
            $previousShortPeriodAverage = null;
            $previousLongPeriodAverage = null;

            $shortPeriodSum = $cryptoPrices->slice(0, $shortPeriod)->sum('close');
            $longPeriodSum = $cryptoPrices->slice(0, $longPeriod)->sum('close');

            foreach ($cryptoPrices as $key => $cryptoPrice) {
                if ($key + 1 < $longPeriod) {
                    continue;
                }

                // If $key + 1 == $longPeriod we have already calculated the $shortPeriodSum and $longPeriodSum. Otherwise, we update the sum by moving the window
                if ($key + 1 > $longPeriod) {
                    $shortPeriodSum = $shortPeriodSum - $cryptoPrices[$key - $shortPeriod]->close + $cryptoPrice->close;
                    $longPeriodSum = $longPeriodSum - $cryptoPrices[$key - $longPeriod]->close + $cryptoPrice->close;
                }

                $shortPeriodAverage = $shortPeriodSum / $shortPeriod;
                $longPeriodAverage = $longPeriodSum / $longPeriod;

                $averages[$cryptoPrice->date_time] = [
                    'close' => $cryptoPrice->close,
                    'short_period_average' => $shortPeriodAverage,
                    'long_period_average' => $longPeriodAverage
                ];


                // At key + 1 == $longPeriod we cannot determine the signal because of the lack of the previous short and long averages
                if ($key + 1 == $longPeriod) {
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
        });

    }
}
