<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'data' => $data,
            'message' => $message,
        ], fn ($value) => $value !== null), $status);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $message,
            'errors' => $errors,
        ], fn ($value) => $value !== null), $status);
    }
}
