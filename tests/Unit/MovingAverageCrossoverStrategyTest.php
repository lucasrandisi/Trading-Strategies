<?php

namespace Tests\Unit;

use App\Strategies\InvestingStrategy;
use App\Strategies\MovingAverageCrossoverStrategy;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MovingAverageCrossoverStrategyTest extends TestCase {
    public function testExecute() {
        $cryptoPrices = new Collection([
            (object)['date_time' => '2023-01-01', 'close' => 100],
            (object)['date_time' => '2023-01-02', 'close' => 100],
            (object)['date_time' => '2023-01-03', 'close' => 100],
            (object)['date_time' => '2023-01-04', 'close' => 100],
            (object)['date_time' => '2023-01-05', 'close' => 100],

            // Price to trigger a SELL signal (short-period average crosses above long-period)
            (object)['date_time' => '2023-01-06', 'close' => 90],

            // Price to trigger a HOLD signal
            (object)['date_time' => '2023-01-07', 'close' => 80],


            // Price to trigger a SELL signal (short-period average crosses below long-period)
            (object)['date_time' => '2023-01-08', 'close' => 110],

            // Price to trigger a HOLD signal
            (object)['date_time' => '2023-01-09', 'close' => 120],
        ]);

        $shortPeriod = 2;
        $longPeriod = 5;
        $initialUSD = 1000;
        $initialCrypto = 0;

        $result = (new MovingAverageCrossoverStrategy($cryptoPrices, $shortPeriod, $longPeriod))->executeTradings($initialUSD, $initialCrypto);

        $this->assertEquals(InvestingStrategy::SELL_SIGNAL, $result['2023-01-06']['signal']);
        $this->assertEquals(InvestingStrategy::HOLD_SIGNAL, $result['2023-01-07']['signal']);
        $this->assertEquals(InvestingStrategy::BUY_SIGNAL, $result['2023-01-09']['signal']);
        $this->assertEquals(InvestingStrategy::HOLD_SIGNAL, $result['2023-01-08']['signal']);
    }
}
