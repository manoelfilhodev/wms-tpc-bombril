<?php

namespace App\Providers;

use App\Events\SecurityEvent;
use App\Services\SecurityAuditService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();

        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->tipo === 'operador') {
                $view->with('layout', 'layouts.layout-operador');
            } else {
                $view->with('layout', 'layouts.app');
            }
        });
        
        Paginator::useBootstrapFive();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip())
                ->response(fn () => $this->rateLimitResponse($request, 'login'));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(optional($request->user())->getAuthIdentifier() ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse($request, 'api'));
        });

        RateLimiter::for('critical', function (Request $request) {
            return Limit::perMinute(20)
                ->by(optional($request->user())->getAuthIdentifier() ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse($request, 'critical'));
        });

        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(30)
                ->by(optional($request->user())->getAuthIdentifier() ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse($request, 'admin'));
        });
    }

    private function rateLimitResponse(Request $request, string $module)
    {
        app(SecurityAuditService::class)->recordSecurityEvent(
            new SecurityEvent(SecurityEvent::RATE_LIMIT_TRIGGERED, ['module' => $module]),
            $request
        );

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Tente novamente em instantes.',
                'data' => (object) [],
                'meta' => [
                    'correlation_id' => $request->attributes->get('correlation_id'),
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 429);
        }

        return response('Muitas tentativas. Tente novamente em instantes.', 429);
    }
}
