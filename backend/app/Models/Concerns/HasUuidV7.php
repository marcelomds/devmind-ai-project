<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuidV7
{
    protected static function bootHasUuidV7(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid7();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
