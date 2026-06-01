<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOperationalRouteIsAuthenticated
{
    /**
     * Defense-in-depth for legacy/duplicated operational routes.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() && $request->is($this->protectedPatterns())) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }

    private function protectedPatterns(): array
    {
        return [
            'armazenagem*',
            'atualizacoes*',
            'container',
            'contagem*',
            'contagem-lista',
            'dashboard*',
            'demandas*',
            'dispositivos*',
            'equipamentos*',
            'estoque*',
            'etiquetas*',
            'expedicao*',
            'formulario',
            'inventario*',
            'imprimir-tudo*',
            'itens/store-multiple',
            'kit*',
            'kits*',
            'liberar-posicao*',
            'logs*',
            'mb52*',
            'multipack*',
            'notificacoes*',
            'painel-operador',
            'painel-tv*',
            'pedidos*',
            'posicoes*',
            'produtos*',
            'recebimento*',
            'relatorios*',
            'saldos',
            'separacoes*',
            'setores*',
            'stretch*',
            'teste-conferencia',
            'transferencias*',
            'usuarios*',
        ];
    }
}
