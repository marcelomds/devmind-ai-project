<?php

namespace App\Repositories\Analysis;

use App\Models\Analysis\Analysis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface AnalysisRepositoryInterface
{
    public function create(array $attributes): Analysis;

    public function findByUuid(string $uuid): Analysis;

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Completed analyses created within [start, end], ordered by created_at ascending.
     *
     * @return Collection<int, Analysis>
     */
    public function completedBetween(Carbon $start, Carbon $end): Collection;
}
