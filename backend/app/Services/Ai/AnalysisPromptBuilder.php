<?php

namespace App\Services\Ai;

use App\Enums\AnalyzerType\AnalyzerType;

class AnalysisPromptBuilder
{
    public function build(AnalyzerType $analyzer, string $language): string
    {
        $focus = match ($analyzer) {
            AnalyzerType::Quality => <<<'PROMPT'
                Focus on code quality: bugs, logic errors, performance issues (e.g. N+1
                queries, unnecessary loops/allocations), security flaws (injection, unsafe
                deserialization, missing validation), and bad patterns (tight coupling,
                duplicated logic, poor error handling).
                PROMPT,
            AnalyzerType::Docs => <<<'PROMPT'
                Focus on documentation and readability: undocumented public functions/classes,
                missing or misleading docblocks, unclear or inconsistent naming, and code whose
                intent is not obvious from reading it.
                PROMPT,
        };

        $language = $this->languageLabel($language);

        return <<<PROMPT
            OUTPUT LANGUAGE: {$language}. Every piece of natural-language text you write —
            title, message, suggestion, and summary — MUST be written in {$language}, with no
            exceptions, regardless of what language the code, comments, or identifiers use. Do
            not translate code, identifiers, enum values, or field names — only the prose.

            You are a senior code reviewer. Review the code the user provides and return your
            findings strictly as the structured JSON the response schema requires.

            {$focus}

            Rules:
            - Be precise and actionable. Every finding must point to a real, specific issue.
            - One finding per issue. Do not bundle unrelated problems together.
            - If you reference a location, use file_path/line_start/line_end when the code
              includes that context; otherwise leave them null.
            - `score` is the overall health of the code from 0 (critical issues, unusable) to
              100 (no issues found), reflecting the severity and number of findings.
            - If the code has no issues for this focus area, return an empty findings array
              and a high score.
            - Reminder: title, message, suggestion, and summary must be written in {$language}.
            PROMPT;
    }

    private function languageLabel(string $code): string
    {
        return match ($code) {
            'pt-BR' => 'Brazilian Portuguese',
            'en' => 'English',
            default => $code,
        };
    }
}
