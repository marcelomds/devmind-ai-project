<?php

namespace App\Repositories\User;

use App\Models\User\User;

interface UserRepositoryInterface
{
    public function create(array $attributes): User;

    public function findByEmail(string $email): ?User;
}
