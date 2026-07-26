<?php

namespace App\Repositories\Analysis;

use App\Models\Analysis\Analysis;

interface AnalysisRepositoryInterface
{
    public function create(array $attributes): Analysis;

    public function findByUuid(string $uuid): Analysis;
}
