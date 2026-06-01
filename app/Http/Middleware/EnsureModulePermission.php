<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $this->allows($user->tipo ?? '', $user->nivel ?? '', $module)) {
            abort(Response::HTTP_FORBIDDEN, 'Usuario sem permissao para acessar este modulo.');
        }

        return $next($request);
    }

    private function allows(string $tipo, string $nivel, string $module): bool
    {
        $tipo = strtolower($tipo);
        $nivel = strtolower($nivel);

        if (in_array($tipo, ['admin', 'gestor', 'supervisor'], true)
            || str_contains($nivel, 'admin')
            || str_contains($nivel, 'gestor')) {
            return true;
        }

        return match ($module) {
            'demandas', 'recebimento', 'kits', 'etiquetas' => $this->isOperational($tipo, $nivel),
            'relatorios' => str_contains($nivel, 'relatorio') || str_contains($nivel, 'supervisor'),
            'administracao' => false,
            default => false,
        };
    }

    private function isOperational(string $tipo, string $nivel): bool
    {
        return $tipo === 'operador'
            || str_contains($tipo, 'operacional')
            || str_contains($nivel, 'operador')
            || str_contains($nivel, 'operacional');
    }
}
