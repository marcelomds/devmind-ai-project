<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Finding\Finding */
class FindingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'severity' => $this->severity->value,
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'suggestion' => $this->suggestion,
            'file_path' => $this->file_path,
            'line_start' => $this->line_start,
            'line_end' => $this->line_end,
        ];
    }
}
