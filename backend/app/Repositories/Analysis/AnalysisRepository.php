<?php

namespace App\Repositories\Analysis;

use App\Exceptions\Analysis\AnalysisNotFoundException;
use App\Models\Analysis\Analysis;

class AnalysisRepository implements AnalysisRepositoryInterface
{
    public function create(array $attributes): Analysis
    {
        return Analysis::create($attributes);
    }

    public function findByUuid(string $uuid): Analysis
    {
        $analysis = Analysis::with('findings')->where('uuid', $uuid)->first();

        if (! $analysis) {
            throw new AnalysisNotFoundException($uuid);
        }

        return $analysis;
    }
}
