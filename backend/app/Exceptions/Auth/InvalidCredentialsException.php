<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;

class InvalidCredentialsException extends ApiException
{
    public function __construct()
    {
        parent::__construct('These credentials do not match our records.');
    }

    public function statusCode(): int
    {
        return 401;
    }
}
