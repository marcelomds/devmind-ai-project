<?php

namespace App\Repositories\Analysis;

use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Exceptions\Analysis\AnalysisNotFoundException;
use App\Models\Analysis\Analysis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalysisRepository implements AnalysisRepositoryInterface
{
    public function create(array $attributes): Analysis
    {
        return Analysis::create($attributes);
    }

    public function findByUuid(string $uuid): Analysis
    {
        $analysis = Analysis::with(['findings', 'repository'])->where('uuid', $uuid)->first();

        if (! $analysis) {
            throw new AnalysisNotFoundException($uuid);
        }

        return $analysis;
    }

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Analysis::with('repository')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['analyzer'] ?? null, fn ($query, $analyzer) => $query->where('analyzer', $analyzer))
            ->when($filters['source_type'] ?? null, fn ($query, $sourceType) => $query->where('source_type', $sourceType))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->latest()
            ->paginate($perPage);
    }

    public function completedBetween(Carbon $start, Carbon $end): Collection
    {
        return Analysis::query()
            ->where('status', AnalysisStatus::Completed->value)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get(['created_at', 'score']);
    }
}
