<?php

namespace App\Services\Analysis;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Exceptions\Analysis\AnalysisNotFoundException;
use App\Jobs\Analysis\RunAnalysis;
use App\Models\Analysis\Analysis;
use App\Repositories\Analysis\AnalysisRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalysisService
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $repository,
    ) {}

    public function create(string $inputCode, AnalyzerType $analyzer, string $language, int $userId): Analysis
    {
        $analysis = $this->repository->create([
            'user_id' => $userId,
            'analyzer' => $analyzer,
            'source_type' => AnalysisSource::Manual,
            'status' => AnalysisStatus::Pending,
            'input_code' => $inputCode,
        ]);

        RunAnalysis::dispatch($analysis, $language);

        return $analysis;
    }

    public function findByUuid(string $uuid, int $userId): Analysis
    {
        $analysis = $this->repository->findByUuid($uuid);

        if ($analysis->user_id !== $userId) {
            throw new AnalysisNotFoundException($uuid);
        }

        return $analysis;
    }

    public function getAll(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }
}
