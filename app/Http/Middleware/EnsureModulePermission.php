<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! method_exists($user, 'hasPermission') || ! $user->hasPermission($permission)) {
            abort(Response::HTTP_FORBIDDEN, 'Usuario sem permissao para acessar este modulo.');
        }

        return $next($request);
    }
}
