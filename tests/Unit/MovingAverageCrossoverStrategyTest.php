<?php

namespace Tests\Unit;

use App\Strategies\MovingAverageCrossoverStrategy;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MovingAverageCrossoverStrategyTest extends TestCase {
    public function testExecute() {
        $cryptoPrices = new Collection([
            // Initial prices to establish a baseline for moving averages
            (object)['date_time' => '2023-01-01', 'close' => 100, 'crypto_id' => 1],
            (object)['date_time' => '2023-01-02', 'close' => 100, 'crypto_id' => 1],
            (object)['date_time' => '2023-01-03', 'close' => 100, 'crypto_id' => 1],
            (object)['date_time' => '2023-01-04', 'close' => 100, 'crypto_id' => 1],
            (object)['date_time' => '2023-01-05', 'close' => 100, 'crypto_id' => 1],

            // Pric to trigger a SELL signal (short-period average crosses above long-period)
            (object)['date_time' => '2023-01-06', 'close' => 90, 'crypto_id' => 1],

            // Price to trigger a HOLD signal
            (object)['date_time' => '2023-01-07', 'close' => 80, 'crypto_id' => 1],


            // Price to trigger a SELL signal (short-period average crosses below long-period)
            (object)['date_time' => '2023-01-08', 'close' => 110, 'crypto_id' => 1],

            // Price to trigger a HOLD signal
            (object)['date_time' => '2023-01-09', 'close' => 120, 'crypto_id' => 1],
        ]);

        $shortPeriod = 2;
        $longPeriod = 5;
        $initialUSD = 1000;
        $initialCrypto = 0;

        $result = MovingAverageCrossoverStrategy::execute($cryptoPrices, $shortPeriod, $longPeriod, $initialUSD, $initialCrypto);

        $this->assertEquals(MovingAverageCrossoverStrategy::SELL_SIGNAL, $result['2023-01-06']['signal']);
        $this->assertEquals(MovingAverageCrossoverStrategy::HOLD_SIGNAL, $result['2023-01-07']['signal']);
        $this->assertEquals(MovingAverageCrossoverStrategy::BUY_SIGNAL, $result['2023-01-09']['signal']);
        $this->assertEquals(MovingAverageCrossoverStrategy::HOLD_SIGNAL, $result['2023-01-08']['signal']);

    }
}
