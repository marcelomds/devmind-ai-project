<?php

namespace App\Services\Ai;

use App\Enums\AnalyzerType\AnalyzerType;
use App\Enums\Severity\Severity;
use App\Services\Ai\Data\AnalysisResult;
use App\Services\Ai\Data\FindingData;

class FakeAiProvider implements AiProvider
{
    public function analyze(string $code, AnalyzerType $analyzer, string $language): AnalysisResult
    {
        return new AnalysisResult(
            score: 72,
            summary: 'Fake analysis: a couple of issues found, nothing blocking.',
            findings: [
                new FindingData(
                    severity: Severity::High,
                    category: 'security',
                    title: 'Fake: possible unsafe input handling',
                    message: 'This is a deterministic fake finding used for testing the analysis pipeline without calling a real AI provider.',
                    suggestion: 'Validate and sanitize the input before use.',
                    filePath: 'app/Example.php',
                    lineStart: 10,
                    lineEnd: 14,
                ),
                new FindingData(
                    severity: Severity::Medium,
                    category: 'performance',
                    title: 'Fake: potential N+1 query',
                    message: 'This is a deterministic fake finding used for testing the analysis pipeline without calling a real AI provider.',
                    suggestion: 'Eager load the relation instead of querying inside the loop.',
                    filePath: 'app/Example.php',
                    lineStart: 22,
                    lineEnd: 27,
                ),
                new FindingData(
                    severity: Severity::Low,
                    category: 'style',
                    title: 'Fake: inconsistent naming',
                    message: 'This is a deterministic fake finding used for testing the analysis pipeline without calling a real AI provider.',
                    suggestion: null,
                    filePath: null,
                    lineStart: null,
                    lineEnd: null,
                ),
            ],
        );
    }
}
