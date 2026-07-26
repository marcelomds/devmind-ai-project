<?php

namespace App\Services\Analysis;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Jobs\Analysis\RunAnalysis;
use App\Models\Analysis\Analysis;
use App\Repositories\Analysis\AnalysisRepositoryInterface;

class AnalysisService
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $repository,
    ) {}

    public function create(string $inputCode, AnalyzerType $analyzer, string $language): Analysis
    {
        $analysis = $this->repository->create([
            'analyzer' => $analyzer,
            'source_type' => AnalysisSource::Manual,
            'status' => AnalysisStatus::Pending,
            'input_code' => $inputCode,
        ]);

        RunAnalysis::dispatch($analysis, $language);

        return $analysis;
    }

    public function findByUuid(string $uuid): Analysis
    {
        return $this->repository->findByUuid($uuid);
    }
}
