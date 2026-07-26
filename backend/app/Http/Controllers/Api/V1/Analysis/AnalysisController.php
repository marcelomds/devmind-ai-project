<?php

namespace App\Http\Controllers\Api\V1\Analysis;

use App\Enums\AnalyzerType\AnalyzerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Analysis\StoreAnalysisRequest;
use App\Http\Resources\Analysis\AnalysisResource;
use App\Services\Analysis\AnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly AnalysisService $analysisService,
    ) {}

    public function store(StoreAnalysisRequest $request): JsonResponse
    {
        $analysis = $this->analysisService->create(
            $request->validated('input_code'),
            AnalyzerType::from($request->validated('analyzer')),
            $request->validated('language'),
        );

        return AnalysisResource::make($analysis)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function show(string $uuid): AnalysisResource
    {
        return AnalysisResource::make($this->analysisService->findByUuid($uuid));
    }
}
