<?php

namespace App\Repositories\Repository;

use App\Exceptions\Repository\RepositoryNotFoundException;
use App\Models\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;

class RepositoryRepository implements RepositoryRepositoryInterface
{
    public function findOrCreateByGithubId(int $githubId, string $fullName, string $name): Repository
    {
        return Repository::firstOrCreate(
            ['github_id' => $githubId],
            ['full_name' => $fullName, 'name' => $name, 'is_active' => true],
        );
    }

    public function findByUuid(string $uuid): Repository
    {
        $repository = Repository::where('uuid', $uuid)->first();

        if (! $repository) {
            throw new RepositoryNotFoundException($uuid);
        }

        return $repository;
    }

    public function all(): Collection
    {
        return Repository::query()->latest()->get();
    }

    public function setActive(Repository $repository, bool $active): Repository
    {
        $repository->update(['is_active' => $active]);

        return $repository;
    }

    public function delete(Repository $repository): void
    {
        $repository->delete();
    }
}
