<?php

namespace App\Services\Repository;

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

    public function connect(string $fullName): Repository
    {
        $data = $this->github->fetchRepository($fullName);

        $repository = $this->repositories->findOrCreateByGithubId($data['id'], $data['full_name'], $data['name']);

        if (! $repository->is_active) {
            $repository = $this->repositories->setActive($repository, true);
        }

        return $repository;
    }

    public function getAll(): Collection
    {
        return $this->repositories->all();
    }

    public function setActive(string $uuid, bool $active): Repository
    {
        return $this->repositories->setActive($this->repositories->findByUuid($uuid), $active);
    }

    public function delete(string $uuid): void
    {
        $this->repositories->delete($this->repositories->findByUuid($uuid));
    }
}
