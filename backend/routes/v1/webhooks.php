<?php

use App\Http\Controllers\Api\V1\Webhook\GithubWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/github', GithubWebhookController::class)
    ->middleware('verify.github.signature')
    ->name('webhooks.github');
