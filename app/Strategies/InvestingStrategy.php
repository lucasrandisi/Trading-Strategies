<?php

namespace App\Strategies;

abstract class InvestingStrategy {
    const BUY_SIGNAL = 'BUY';
    const SELL_SIGNAL = 'SELL';
    const HOLD_SIGNAL = 'HOLD';

    public function executeTradings(float $initialUSD, float $initialCrypto) {
        $signals = $this->calculateSignals();

        $tradings = [];
        $previousUSD = $initialUSD;
        $previousCrypto = $initialCrypto;

        foreach ($signals as $dateTime => $signalInfo) {
            if ($signalInfo['signal'] === self::HOLD_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => $previousUSD,
                    'crypto' => $previousCrypto
                ];
            } else if ($signalInfo['signal'] === self::BUY_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => 0,
                    'crypto' => $previousCrypto + $previousUSD / $signalInfo['close']
                ];
            } else if ($signalInfo['signal'] === self::SELL_SIGNAL) {
                $tradings[$dateTime] = [
                    'usd' => $previousUSD + $previousCrypto * $signalInfo['close'],
                    'crypto' => 0
                ];
            }

            $tradings[$dateTime]['signal'] = $signalInfo['signal'];
            $tradings[$dateTime]['close'] = $signalInfo['close'];
            $tradings[$dateTime]['total_value'] = $tradings[$dateTime]['usd'] + $tradings[$dateTime]['crypto'] * $signalInfo['close'];

            $previousUSD = $tradings[$dateTime]['usd'];
            $previousCrypto = $tradings[$dateTime]['crypto'];
        }

        return $tradings;
    }

    abstract protected function calculateSignals();
}
