<?php

namespace App\Services\Repository;

use App\Exceptions\Repository\RepositoryNotFoundException;
use App\Models\Repository\Repository;
use App\Repositories\Repository\RepositoryRepositoryInterface;
use App\Services\Github\GithubClient;
use Illuminate\Database\Eloquent\Collection;

class RepositoryService
{
    public function __construct(
        private readonly RepositoryRepositoryInterface $repositories,
        private readonly GithubClient $github,
    ) {}

    public function connect(string $fullName, int $userId): Repository
    {
        $data = $this->github->fetchRepository($fullName);

        $repository = $this->repositories->findOrCreateByGithubId($data['id'], $data['full_name'], $data['name']);

        if (! $repository->is_active) {
            $repository = $this->repositories->setActive($repository, true);
        }

        if ($repository->user_id === null) {
            $repository = $this->repositories->assignUser($repository, $userId);
        }

        return $repository;
    }

    public function getAll(int $userId): Collection
    {
        return $this->repositories->forUser($userId);
    }

    public function setActive(string $uuid, bool $active, int $userId): Repository
    {
        return $this->repositories->setActive($this->findOwnedByUuid($uuid, $userId), $active);
    }

    public function delete(string $uuid, int $userId): void
    {
        $this->repositories->delete($this->findOwnedByUuid($uuid, $userId));
    }

    private function findOwnedByUuid(string $uuid, int $userId): Repository
    {
        $repository = $this->repositories->findByUuid($uuid);

        if ($repository->user_id !== $userId) {
            throw new RepositoryNotFoundException($uuid);
        }

        return $repository;
    }
}
