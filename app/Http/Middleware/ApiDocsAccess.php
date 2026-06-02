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

        if (! $enabledByFlag) {
            abort(404);
        }

        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! method_exists($user, 'hasPermission') || ! $user->hasPermission('admin.access')) {
            abort(403);
        }

        return $next($request);
    }
}
