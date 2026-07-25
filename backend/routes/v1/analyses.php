<?php

use App\Http\Controllers\Api\V1\Analysis\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::post('/analyses', [AnalysisController::class, 'store'])
    ->name('analyses.store');

Route::get('/analyses/{analysis}', [AnalysisController::class, 'show'])
    ->name('analyses.show');
