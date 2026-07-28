<?php

namespace App\Http\Controllers\Api\V1\Stats;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stats\StatsRequest;
use App\Http\Resources\Stats\StatsResource;
use App\Services\Stats\StatsService;

class StatsController extends Controller
{
    public function __construct(
        private readonly StatsService $statsService,
    ) {}

    public function __invoke(StatsRequest $request): StatsResource
    {
        $stats = $this->statsService->getStats((int) $request->validated('days', 30));

        return StatsResource::make($stats);
    }
}
