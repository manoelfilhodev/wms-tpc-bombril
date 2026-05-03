<?php

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
        $middleware->api(append: [
            App\Http\Middleware\ApiRequestLogger::class,
        ]);
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'demanda.perfil' => App\Http\Middleware\DemandaPerfilMiddleware::class,
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
                'meta' => (object) [],
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
                'meta' => (object) [],
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
                'meta' => (object) [],
            ], 401);
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
                'meta' => (object) [],
            ], $statusCode);
        });
    })->create();
