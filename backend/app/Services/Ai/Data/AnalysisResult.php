<?php

namespace App\Services\Ai\Data;

final readonly class AnalysisResult
{
    /**
     * @param  FindingData[]  $findings
     */
    public function __construct(
        public int $score,
        public string $summary,
        public array $findings,
    ) {}
}
