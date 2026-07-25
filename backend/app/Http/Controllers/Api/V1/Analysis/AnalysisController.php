<?php

namespace App\Http\Controllers\Api\V1\Analysis;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnalysisRequest;
use App\Http\Resources\AnalysisResource;
use App\Jobs\RunAnalysis;
use App\Models\Analysis\Analysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    public function store(StoreAnalysisRequest $request): JsonResponse
    {
        $analysis = Analysis::create([
            'analyzer' => $request->validated('analyzer'),
            'source_type' => AnalysisSource::Manual,
            'status' => AnalysisStatus::Pending,
            'input_code' => $request->validated('input_code'),
        ]);

        RunAnalysis::dispatch($analysis);

        return AnalysisResource::make($analysis)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function show(Analysis $analysis): AnalysisResource
    {
        return AnalysisResource::make($analysis->load('findings'));
    }
}
