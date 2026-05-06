<?php

namespace App\Traits;

use App\Constants\SystemCode;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Standard success response envelope.
     */
    public function success(mixed $data = [], string $message = 'Success', int $code = 200, string $systemCode = SystemCode::SUCCESS): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => $systemCode,
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ], $code);
    }

    /**
     * Standard error response envelope.
     */
    public function error(string $message = 'Error', int $code = 400, mixed $errors = null, string $systemCode = SystemCode::ERR_GENERIC): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => $systemCode,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
