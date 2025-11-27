<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

abstract class BaseApiController extends Controller
{
    protected function success(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }

    protected function data(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }

    protected function created(mixed $data = null, array $headers = []): JsonResponse
    {
        return response()->json($data ?? ['ok' => true], 201, $headers);
    }

    protected function noContent(): JsonResponse
    {
        // 204 responses must not include a body
        return response()->json(null, 204);
    }

    protected function paginated($paginator, array $extra = [], int $status = 200): JsonResponse
    {
        // Works with LengthAwarePaginator / Paginator JSON serialization
        // Adds a consistent wrapper if you want it.
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => method_exists($paginator, 'total') ? $paginator->total() : null,
                'last_page' => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
            ],
            ...$extra,
        ], $status);
    }

    protected function fail(string $message, int $status = 400, array $errors = [], array $extra = []): JsonResponse
    {
        $payload = ['message' => $message];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload + $extra, $status);
    }

    protected function unauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->fail($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->fail($message, 403);
    }

    protected function notFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->fail($message, 404);
    }
}
