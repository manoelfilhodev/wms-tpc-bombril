<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApontamentoPaleteStretchRequest;
use App\Models\ApontamentoPaleteStretch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApontamentoPaleteStretchController extends Controller
{
    public function index(): View
    {
        $tabelaDisponivel = Schema::hasTable('_tb_apontamentos_paletes_stretch');

        if (! $tabelaDisponivel) {
            $apontamentos = new LengthAwarePaginator([], 0, 20);

            session()->flash(
                'warning',
                'A tabela de apontamentos Stretch ainda nao existe. Execute php artisan migrate para habilitar o registro.'
            );

            return view('stretch.apontar', compact('apontamentos', 'tabelaDisponivel'));
        }

        $apontamentos = ApontamentoPaleteStretch::with('usuario')
            ->latest('apontado_em_servidor')
            ->paginate(20);

        return view('stretch.apontar', compact('apontamentos', 'tabelaDisponivel'));
    }

    public function store(StoreApontamentoPaleteStretchRequest $request): RedirectResponse
    {
        if (! Schema::hasTable('_tb_apontamentos_paletes_stretch')) {
            return redirect()
                ->route('stretch.apontar')
                ->with('warning', 'A tabela de apontamentos Stretch ainda nao existe. Execute php artisan migrate para habilitar o registro.');
        }

        $user = $request->user();

        ApontamentoPaleteStretch::create([
            'palete_codigo' => $request->validated('palete_codigo'),
            'usuario_id' => $user?->id_user,
            'unidade_id' => $user?->unidade_id,
            'status' => 'APONTADO',
            'origem' => 'WEB',
            'observacao' => $request->validated('observacao'),
            'client_uuid' => (string) Str::uuid(),
            'apontado_em_servidor' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('stretch.apontar')
            ->with('success', 'Palete apontado com stretch com sucesso.');
    }
}
