<?php

namespace App\Exceptions\Repository;

use App\Exceptions\ApiException;

class RepositoryNotFoundException extends ApiException
{
    public function __construct(string $uuid)
    {
        parent::__construct("Repository [{$uuid}] not found.");
    }

    public function statusCode(): int
    {
        return 404;
    }
}
