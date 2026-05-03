<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->tipo === 'operador') {
                $view->with('layout', 'layouts.layout-operador');
            } else {
                $view->with('layout', 'layouts.app');
            }
        });
        
        Paginator::useBootstrapFive();
    }
}
