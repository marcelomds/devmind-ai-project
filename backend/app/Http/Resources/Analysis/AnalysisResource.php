<?php

namespace App\Http\Resources\Analysis;

use App\Models\Analysis\Analysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Analysis */
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
            'pr_title' => $this->pr_title,
            'commit_sha' => $this->commit_sha,
            'pr_author_login' => $this->pr_author_login,
            'pr_author_avatar_url' => $this->pr_author_avatar_url,
            'pr_author_github_id' => $this->pr_author_github_id,
            'repository_full_name' => $this->whenLoaded('repository', fn () => $this->repository?->full_name),
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
