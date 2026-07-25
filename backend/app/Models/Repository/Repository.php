<?php

namespace App\Models\Repository;

use App\Models\Analysis\Analysis;
use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repository extends Model
{
    use HasUuidV7, SoftDeletes;

    protected $fillable = [
        'user_id',
        'github_id',
        'name',
        'full_name',
        'webhook_secret',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
