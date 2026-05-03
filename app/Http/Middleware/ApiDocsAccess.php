<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabledByFlag = filter_var(
            config('scribe.wms_docs_enabled', false),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) === true;

        $enabled = app()->environment('local') || $enabledByFlag;

        if (! $enabled) {
            abort(404);
        }

        return $next($request);
    }
}
