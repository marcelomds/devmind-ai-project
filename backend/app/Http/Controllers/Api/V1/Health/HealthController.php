<?php

namespace App\Http\Controllers\Api\V1\Health;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'devmind-ai',
            'version' => '1.0.0',
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
