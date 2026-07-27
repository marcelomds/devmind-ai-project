<?php

namespace App\Repositories\Analysis;

use App\Models\Analysis\Analysis;
use Illuminate\Pagination\LengthAwarePaginator;

interface AnalysisRepositoryInterface
{
    public function create(array $attributes): Analysis;

    public function findByUuid(string $uuid): Analysis;

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;
}
