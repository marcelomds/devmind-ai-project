<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        require __DIR__.'/v1/health.php';
        require __DIR__.'/v1/auth.php';
        require __DIR__.'/v1/analyses.php';
        require __DIR__.'/v1/repositories.php';
        require __DIR__.'/v1/webhooks.php';
        require __DIR__.'/v1/stats.php';
    });
