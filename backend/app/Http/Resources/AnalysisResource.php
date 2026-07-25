<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Analysis\Analysis */
class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'analyzer' => $this->analyzer->value,
            'status' => $this->status->value,
            'source_type' => $this->source_type->value,
            'pr_number' => $this->pr_number,
            'commit_sha' => $this->commit_sha,
            'summary' => $this->summary,
            'score' => $this->score,
            'error_message' => $this->error_message,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'created_at' => $this->created_at,
            'findings' => FindingResource::collection($this->whenLoaded('findings')),
        ];
    }
}
