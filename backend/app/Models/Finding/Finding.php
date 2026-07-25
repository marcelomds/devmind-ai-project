<?php

namespace App\Models\Finding;

use App\Enums\Severity\Severity;
use App\Models\Analysis\Analysis;
use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finding extends Model
{
    use HasUuidV7;

    protected $fillable = [
        'analysis_id',
        'severity',
        'category',
        'title',
        'message',
        'suggestion',
        'file_path',
        'line_start',
        'line_end',
    ];

    protected function casts(): array
    {
        return [
            'severity' => Severity::class,
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
