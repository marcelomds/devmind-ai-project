<?php

use App\Http\Controllers\Api\V1\Analysis\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analyses', [AnalysisController::class, 'index'])
        ->name('analyses.index');

    Route::post('/analyses', [AnalysisController::class, 'store'])
        ->name('analyses.store');

    Route::get('/analyses/{uuid}', [AnalysisController::class, 'show'])
        ->name('analyses.show');
});
