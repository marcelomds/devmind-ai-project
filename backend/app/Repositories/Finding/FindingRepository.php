<?php

namespace App\Repositories\Finding;

use App\Models\Analysis\Analysis;

class FindingRepository implements FindingRepositoryInterface
{
    public function createMany(Analysis $analysis, array $findings): void
    {
        $analysis->findings()->createMany($findings);
    }
}
