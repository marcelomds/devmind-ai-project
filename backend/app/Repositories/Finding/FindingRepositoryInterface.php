<?php

namespace App\Repositories\Finding;

use App\Models\Analysis\Analysis;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface FindingRepositoryInterface
{
    public function createMany(Analysis $analysis, array $findings): void;

    /**
     * Finding counts grouped by severity, for findings whose analysis is completed
     * and was created within [start, end]. Keyed by severity value.
     *
     * @return Collection<string, int>
     */
    public function severityCountsBetween(Carbon $start, Carbon $end): Collection;
}
