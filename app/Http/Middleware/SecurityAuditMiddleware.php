<?php

namespace App\Http\Middleware;

use App\Services\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;

class SecurityAuditMiddleware
{
    public function __construct(private readonly SecurityAuditService $audit)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $this->audit->record($this->actionFor($request), null, [], $request);
        }

        return $response;
    }

    private function actionFor(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'status')) {
            return 'status_changed';
        }

        return match ($request->method()) {
            'POST' => 'created_or_executed',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'accessed',
        };
    }
}
