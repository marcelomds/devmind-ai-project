<?php

use App\Http\Controllers\Api\V1\Repository\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/repositories', [RepositoryController::class, 'index'])
        ->name('repositories.index');

    Route::post('/repositories', [RepositoryController::class, 'store'])
        ->name('repositories.store');

    Route::patch('/repositories/{uuid}', [RepositoryController::class, 'update'])
        ->name('repositories.update');

    Route::delete('/repositories/{uuid}', [RepositoryController::class, 'destroy'])
        ->name('repositories.destroy');
});
