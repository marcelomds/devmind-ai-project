<?php

namespace App\Services\Ai\Data;

use App\Enums\Severity\Severity;

final readonly class FindingData
{
    public function __construct(
        public Severity $severity,
        public string $category,
        public string $title,
        public string $message,
        public ?string $suggestion,
        public ?string $filePath,
        public ?int $lineStart,
        public ?int $lineEnd,
    ) {}
}
