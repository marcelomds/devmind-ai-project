<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiException extends Exception
{
    abstract public function statusCode(): int;

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->statusCode());
    }
}
