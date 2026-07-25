<?php

namespace App\Services\Ai;

use App\Enums\AnalyzerType\AnalyzerType;
use App\Services\Ai\Data\AnalysisResult;

interface AiProvider
{
    public function analyze(string $code, AnalyzerType $analyzer): AnalysisResult;
}
