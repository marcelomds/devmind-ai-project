<?php

use App\Http\Controllers\Api\V1\Stats\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/stats', StatsController::class)
        ->name('stats');
});
