<?php

namespace App\Repositories\Finding;

use App\Models\Analysis\Analysis;

interface FindingRepositoryInterface
{
    public function createMany(Analysis $analysis, array $findings): void;
}
