<?php

namespace App\Repositories\Repository;

use App\Models\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryRepositoryInterface
{
    public function findOrCreateByGithubId(int $githubId, string $fullName, string $name): Repository;

    public function findByUuid(string $uuid): Repository;

    public function forUser(int $userId): Collection;

    public function setActive(Repository $repository, bool $active): Repository;

    public function assignUser(Repository $repository, int $userId): Repository;

    public function delete(Repository $repository): void;
}
