<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasPermission') || ! $user->hasPermission('admin.access')) {
            abort(Response::HTTP_FORBIDDEN, 'Acesso nao autorizado.');
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $request->session()->migrate(true);
        }

        return $next($request);
    }
}
