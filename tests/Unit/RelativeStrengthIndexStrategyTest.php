<?php

namespace Tests\Unit;

use App\Strategies\InvestingStrategy;
use App\Strategies\RelativeStrengthIndexStrategy;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class RelativeStrengthIndexStrategyTest extends TestCase {
    public function testExecute(): void {
        $cryptoPrices = new Collection([
            (object)['date_time' => '2023-01-01', 'close' => 100],
            (object)['date_time' => '2023-01-02', 'close' => 150],
            (object)['date_time' => '2023-01-03', 'close' => 150],
            (object)['date_time' => '2023-01-04', 'close' => 100],
            (object)['date_time' => '2023-01-05', 'close' => 80],
            (object)['date_time' => '2023-01-06', 'close' => 60],
            (object)['date_time' => '2023-01-07', 'close' => 40],
            (object)['date_time' => '2023-01-08', 'close' => 70],
            (object)['date_time' => '2023-01-09', 'close' => 90],
            (object)['date_time' => '2023-01-10', 'close' => 100],
            (object)['date_time' => '2023-01-11', 'close' => 120],
        ]);

        $periods = 4;
        $initialUSD = 1000;
        $initialCrypto = 0;


        $tradings = (new RelativeStrengthIndexStrategy($cryptoPrices, $periods))->executeTradings($initialUSD, $initialCrypto);

        $this->assertEquals(InvestingStrategy::HOLD_SIGNAL, $tradings['2023-01-04']['signal']);
        $this->assertEquals(InvestingStrategy::BUY_SIGNAL, $tradings['2023-01-07']['signal']);
        $this->assertEquals(InvestingStrategy::SELL_SIGNAL, $tradings['2023-01-11']['signal']);
    }
}
