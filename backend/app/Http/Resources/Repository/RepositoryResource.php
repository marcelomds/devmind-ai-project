<?php

namespace App\Http\Resources\Repository;

use App\Models\Repository\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Repository */
class RepositoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'github_id' => $this->github_id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
