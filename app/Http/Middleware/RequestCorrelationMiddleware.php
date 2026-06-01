<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestCorrelationMiddleware
{
    public const CORRELATION_ID = 'correlation_id';
    public const REQUEST_ID = 'request_id';

    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->headers->get('X-Correlation-Id') ?: (string) Str::uuid();
        $requestId = (string) Str::uuid();

        $request->attributes->set(self::CORRELATION_ID, $correlationId);
        $request->attributes->set(self::REQUEST_ID, $requestId);
        Log::withContext([
            self::CORRELATION_ID => $correlationId,
            self::REQUEST_ID => $requestId,
        ]);

        $response = $next($request);

        $response->headers->set('X-Correlation-Id', $correlationId);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
