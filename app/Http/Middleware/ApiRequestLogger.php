<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $user = $request->user();
        $usuarioId = $user->id_user ?? $user->id ?? null;
        $unidadeId = $user->unidade_id ?? null;

        if ($usuarioId !== null && $unidadeId !== null) {
            $dados = [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
                'duration_ms' => $durationMs,
                'status' => $response->getStatusCode(),
            ];

            try {
                DB::table('_tb_user_logs')->insert([
                    'usuario_id' => $usuarioId,
                    'unidade_id' => $unidadeId,
                    'acao' => 'api_request',
                    'dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip_address' => $request->ip(),
                    'navegador' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $response;
    }
}
