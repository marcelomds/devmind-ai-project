<?php

use App\Http\Controllers\Api\V1\Health\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)
    ->name('health');