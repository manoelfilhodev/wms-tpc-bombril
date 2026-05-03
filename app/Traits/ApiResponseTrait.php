<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function success($data = [], $message = null, $meta = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->normalizePayload($data),
            'meta' => $this->normalizePayload($meta),
        ]);
    }

    public function error($message = null, $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => (object) [],
            'meta' => (object) [],
        ], $code);
    }

    private function normalizePayload($payload)
    {
        if (is_array($payload) && $payload === []) {
            return (object) [];
        }

        return $payload;
    }
}
