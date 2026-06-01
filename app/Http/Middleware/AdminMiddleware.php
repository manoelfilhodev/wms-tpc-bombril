<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (session('tipo') !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Acesso nao autorizado.');
        }

        return $next($request);
    }
}
