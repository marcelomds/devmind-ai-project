<?php

return [
    'token' => env('GITHUB_TOKEN'),

    'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),

    'max_diff_length' => env('GITHUB_MAX_DIFF_LENGTH', 50000),
];
