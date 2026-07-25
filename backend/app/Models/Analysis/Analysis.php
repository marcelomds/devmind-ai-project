<?php

namespace App\Models\Analysis;

use App\Enums\AnalysisSource\AnalysisSource;
use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Enums\AnalyzerType\AnalyzerType;
use App\Models\Concerns\HasUuidV7;
use App\Models\Finding\Finding;
use App\Models\Repository\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    use HasUuidV7;

    protected $fillable = [
        'repository_id',
        'analyzer',
        'status',
        'source_type',
        'pr_number',
        'commit_sha',
        'input_code',
        'summary',
        'score',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'analyzer' => AnalyzerType::class,
            'status' => AnalysisStatus::class,
            'source_type' => AnalysisSource::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }
}
