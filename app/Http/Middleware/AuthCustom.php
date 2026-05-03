<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthCustom
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
