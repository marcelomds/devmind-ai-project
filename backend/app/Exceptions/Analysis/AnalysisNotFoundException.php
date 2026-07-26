<?php

namespace App\Exceptions\Analysis;

use App\Exceptions\ApiException;

class AnalysisNotFoundException extends ApiException
{
    public function __construct(string $uuid)
    {
        parent::__construct("Analysis [{$uuid}] not found.");
    }

    public function statusCode(): int
    {
        return 404;
    }
}
