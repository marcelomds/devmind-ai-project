<?php

namespace App\Services\Stats;

use App\Enums\Severity\Severity;
use App\Repositories\Analysis\AnalysisRepositoryInterface;
use App\Repositories\Finding\FindingRepositoryInterface;
use Illuminate\Support\Carbon;

class StatsService
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly FindingRepositoryInterface $findings,
    ) {}

    public function getStats(int $days = 30): array
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subDays($days);
        $previousStart = $now->copy()->subDays($days * 2);
        $previousEnd = $windowStart;

        $current = $this->analyses->completedBetween($windowStart, $now);
        $previous = $this->analyses->completedBetween($previousStart, $previousEnd);

        $currentSeverity = $this->findings->severityCountsBetween($windowStart, $now);
        $previousSeverity = $previous->isEmpty()
            ? null
            : $this->findings->severityCountsBetween($previousStart, $previousEnd);

        $currentScore = $current->last()?->score;
        $previousAnalysisScore = $current->count() >= 2
            ? $current->slice(-2, 1)->first()?->score
            : null;

        $criticalCount = (int) ($currentSeverity[Severity::Critical->value] ?? 0);
        $previousCriticalCount = $previousSeverity !== null
            ? (int) ($previousSeverity[Severity::Critical->value] ?? 0)
            : null;

        return [
            'summary' => [
                'current_score' => $currentScore !== null ? (int) round($currentScore) : null,
                'score_delta' => $currentScore !== null && $previousAnalysisScore !== null
                    ? (int) round($currentScore - $previousAnalysisScore)
                    : null,
                'total_analyses' => $current->count(),
                'open_findings' => (int) $currentSeverity->sum(),
                'critical_count' => $criticalCount,
                'critical_delta' => $previousCriticalCount !== null
                    ? $criticalCount - $previousCriticalCount
                    : null,
            ],
            'score_over_time' => $current->map(fn ($analysis) => [
                'date' => $analysis->created_at->toDateString(),
                'score' => $analysis->score !== null ? (int) round($analysis->score) : null,
            ])->values()->all(),
            'findings_by_severity' => collect(Severity::cases())->map(fn (Severity $severity) => [
                'severity' => $severity->value,
                'count' => (int) ($currentSeverity[$severity->value] ?? 0),
            ])->values()->all(),
        ];
    }
}
