<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_id')->references('id')->on('cryptos');
            $table->dateTime('date_time');
            $table->float('open');
            $table->float('close');
            $table->float('low');
            $table->float('high');
            $table->unsignedBigInteger('volume');

            $table->unique(['crypto_id', 'date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('crypto_prices');
    }
};
