<?php

namespace App\Repositories\Finding;

use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Models\Analysis\Analysis;
use App\Models\Finding\Finding;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FindingRepository implements FindingRepositoryInterface
{
    public function createMany(Analysis $analysis, array $findings): void
    {
        $analysis->findings()->createMany($findings);
    }

    public function severityCountsBetween(Carbon $start, Carbon $end): Collection
    {
        return Finding::query()
            ->join('analyses', 'analyses.id', '=', 'findings.analysis_id')
            ->where('analyses.status', AnalysisStatus::Completed->value)
            ->whereBetween('analyses.created_at', [$start, $end])
            ->selectRaw('findings.severity as severity, COUNT(*) as count')
            ->groupBy('findings.severity')
            ->pluck('count', 'severity');
    }
}
