<?php

use App\Services\DeviceAuthorizationService;
use App\Events\SecurityEvent;
use App\Services\SecurityAuditService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,

        // Essenciais
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,

        // Adicionado para corrigir o erro de traducao
        Illuminate\Translation\TranslationServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: [
            DeviceAuthorizationService::COOKIE_NAME,
            DeviceAuthorizationService::LEGACY_COOKIE_NAME,
        ]);

        $middleware->api(append: [
            App\Http\Middleware\RequestCorrelationMiddleware::class,
            App\Http\Middleware\BlockMaliciousUploads::class,
            App\Http\Middleware\SecurityHeaders::class,
            App\Http\Middleware\SecurityAuditMiddleware::class,
            App\Http\Middleware\ApiRequestLogger::class,
        ]);
        $middleware->web(append: [
            App\Http\Middleware\RequestCorrelationMiddleware::class,
            App\Http\Middleware\BlockMaliciousUploads::class,
            App\Http\Middleware\SecurityHeaders::class,
            App\Http\Middleware\EnsureOperationalRouteIsAuthenticated::class,
            App\Http\Middleware\SecurityAuditMiddleware::class,
        ]);
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'demanda.perfil' => App\Http\Middleware\DemandaPerfilMiddleware::class,
            'module.permission' => App\Http\Middleware\EnsureModulePermission::class,
            'permission' => App\Http\Middleware\EnsureModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $isApiRequest = static fn (Request $request): bool =>
            $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $exception) use ($isApiRequest): bool {
            return $isApiRequest($request);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Recurso nao encontrado.',
                'data' => (object) [],
                'meta' => [
                    'correlation_id' => $request->attributes->get('correlation_id'),
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 404);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro de validacao.',
                'data' => [
                    'errors' => $exception->errors(),
                ],
                'meta' => [
                    'correlation_id' => $request->attributes->get('correlation_id'),
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Nao autenticado.',
                'data' => (object) [],
                'meta' => [
                    'correlation_id' => $request->attributes->get('correlation_id'),
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 401);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() === 403) {
                app(SecurityAuditService::class)->recordSecurityEvent(
                    new SecurityEvent(SecurityEvent::PERMISSION_DENIED, [
                        'module' => 'authorization',
                        'path' => $request->path(),
                    ]),
                    $request
                );
            }

            return null;
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $message = config('app.debug')
                ? $exception->getMessage()
                : ($statusCode === 500 ? 'Erro interno do servidor.' : ($exception->getMessage() ?: 'Erro na requisicao.'));

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => (object) [],
                'meta' => [
                    'correlation_id' => $request->attributes->get('correlation_id'),
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], $statusCode);
        });
    })->create();
