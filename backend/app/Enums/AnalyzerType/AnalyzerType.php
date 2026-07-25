<?php

namespace App\Enums\AnalyzerType;

enum AnalyzerType: string
{
    case Quality = 'quality';
    case Docs = 'docs';

    public function label(): string
    {
        return match ($this) {
            self::Quality => 'Quality',
            self::Docs => 'Docs',
        };
    }
}
