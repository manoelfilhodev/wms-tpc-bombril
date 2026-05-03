<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (session('tipo') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        return $next($request);
    }
}
