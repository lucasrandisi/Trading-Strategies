<?php

use App\Http\Controllers\StrategiesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('strategies')->group(function () {
    Route::get('moving-average-crossover', [StrategiesController::class, 'movingAverageCrossover']);
    Route::get('relative-strength-index', [StrategiesController::class, 'relativeStrengthIndex']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
