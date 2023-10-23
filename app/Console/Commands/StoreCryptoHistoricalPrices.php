<?php

namespace App\Console\Commands;

use App\Clients\FMPClient;
use App\Services\CryptoPriceService;
use App\Services\CryptoService;
use Illuminate\Console\Command;

class StoreCryptoHistoricalPrices extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store-crypto-historical-prices {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve and store crypto historical prices';

    /**
     * Execute the console command.
     */
    public function handle() {
        $name = $this->argument('name');

        $response = FMPClient::request("/v3/historical-price-full/{$name}USD");

        $crypto = CryptoService::store($name);

        $prices = $response['historical'];
        usort($prices, function ($a, $b) {
            $timestampA = strtotime($a['date']);
            $timestampB = strtotime($b['date']);

            return $timestampA - $timestampB;
        });

        foreach ($prices as $price) {
            CryptoPriceService::store(
                $crypto['id'],
                $price['date'],
                $price['open'],
                $price['close'],
                $price['low'],
                $price['high'],
                $price['volume']
            );
        }
    }
}
