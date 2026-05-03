<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DemandaPerfilMiddleware
{
    public function handle(Request $request, Closure $next, string $escopo = 'operacional')
    {
        $tipo = strtolower((string) session('tipo', ''));
        $nivel = strtolower((string) session('nivel', ''));

        if ($this->isAdmin($tipo, $nivel)) {
            return $next($request);
        }

        $permitido = match ($escopo) {
            'sala' => $this->isAdmSala($tipo, $nivel),
            'operacional' => $this->isAdmOperacional($tipo, $nivel),
            default => false,
        };

        if (! $permitido) {
            return redirect()->route('demandas.index')->with('error', 'Acesso não autorizado para esta ação.');
        }

        return $next($request);
    }

    private function isAdmin(string $tipo, string $nivel): bool
    {
        return in_array($tipo, ['admin', 'gestor', 'supervisor'], true)
            || str_contains($nivel, 'admin')
            || str_contains($nivel, 'gestor');
    }

    private function isAdmSala(string $tipo, string $nivel): bool
    {
        return str_contains($tipo, 'sala')
            || str_contains($nivel, 'sala')
            || str_contains($nivel, 'adm_sala');
    }

    private function isAdmOperacional(string $tipo, string $nivel): bool
    {
        return $tipo === 'operador'
            || str_contains($tipo, 'operacional')
            || str_contains($nivel, 'operacional')
            || str_contains($nivel, 'adm_operacional');
    }
}
