<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus\AnalysisStatus;
use App\Models\Analysis\Analysis;
use App\Services\Ai\AiProvider;
use App\Services\Ai\Exceptions\AiRefusalException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 90;

    public function __construct(
        public readonly Analysis $analysis,
    ) {}

    public function handle(AiProvider $ai): void
    {
        $this->analysis->update([
            'status' => AnalysisStatus::Processing,
            'started_at' => now(),
        ]);

        try {
            $result = $ai->analyze($this->analysis->input_code, $this->analysis->analyzer);
        } catch (AiRefusalException $e) {
            $this->fail($e);

            return;
        }

        $this->analysis->findings()->createMany(array_map(fn ($finding) => [
            'severity' => $finding->severity,
            'category' => $finding->category,
            'title' => $finding->title,
            'message' => $finding->message,
            'suggestion' => $finding->suggestion,
            'file_path' => $finding->filePath,
            'line_start' => $finding->lineStart,
            'line_end' => $finding->lineEnd,
        ], $result->findings));

        $this->analysis->update([
            'summary' => $result->summary,
            'score' => $result->score,
            'status' => AnalysisStatus::Completed,
            'finished_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        $this->analysis->update([
            'status' => AnalysisStatus::Failed,
            'error_message' => $e->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
