<?php

namespace App\Enums\Severity;

enum Severity: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'info';

    public function label(): string
    {
        return match ($this) {
            self::Critical => 'Critical',
            self::High => 'High',
            self::Medium => 'Medium',
            self::Low => 'Low',
            self::Info => 'Info',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Critical => 'red',
            self::High => 'orange',
            self::Medium => 'yellow',
            self::Low => 'blue',
            self::Info => 'gray',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Critical => 5,
            self::High => 4,
            self::Medium => 3,
            self::Low => 2,
            self::Info => 1,
        };
    }
}
