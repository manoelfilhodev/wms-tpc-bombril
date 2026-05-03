<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Demanda;
use App\Models\DemandaHistory;
use Carbon\Carbon;

class DemandaController extends Controller
{
    /**
     * Lista todas as demandas (somente as da data atual).
     */
    public function index()
    {
        $hoje = Carbon::today();

        $demandas = Demanda::whereDate('created_at', $hoje)
            ->get([
                'id',
                'fo',
                'cliente',
                'transportadora',
                'veiculo',
                'status',
                'tipo',
                'quantidade',
                'doca',
                'hora_agendada'
            ]);

        return response()->json($demandas);
    }

    /**
     * Retorna detalhes de uma demanda específica (somente se for da data atual).
     */
    public function show($id)
    {
        $hoje = Carbon::today();

        $demanda = Demanda::where('id', $id)
            ->whereDate('created_at', $hoje)
            ->firstOrFail();

        return response()->json([
            'id'            => $demanda->id,
            'fo'            => $demanda->fo,
            'cliente'       => $demanda->cliente,
            'transportadora'=> $demanda->transportadora,
            'veiculo'       => $demanda->veiculo,
            'status'        => $demanda->status,
            'tipo'          => $demanda->tipo,
            'quantidade'    => $demanda->quantidade,
            'doca'          => $demanda->doca,
            'hora_agendada' => $demanda->hora_agendada,
            'entrada'       => $demanda->entrada,
            'saida'         => $demanda->saida,
            'peso'          => $demanda->peso,
            'valor_carga'   => $demanda->valor_carga,
            'created_at'    => $demanda->created_at->format('d/m/Y'),
            'updated_at'    => $demanda->updated_at->format('d/m/Y'),
        ]);
    }

    /**
     * Atualiza o status de uma demanda e salva no histórico.
     */
    public function atualizarStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:SEPARANDO,A_CONFERIR,CONFERINDO,CONFERIDO,A_CARREGAR,CARREGANDO,CARREGADO',
        ]);

        $demanda = Demanda::findOrFail($id);
        $demanda->status = $request->status;
        $demanda->save();

        DemandaHistory::create([
            'demanda_id' => $demanda->id,
            'status'     => $request->status,
            'changed_by' => auth()->check() ? auth()->id() : null,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $demanda->status,
        ]);
    }

    /**
     * Atualiza dados gerais da demanda (ex: doca, veículo, hora agendada).
     */
    public function update(Request $request, $id)
    {
        $demanda = Demanda::findOrFail($id);

        $demanda->update($request->only([
            'doca',
            'veiculo',
            'hora_agendada'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Demanda atualizada com sucesso.',
            'demanda' => $demanda,
        ]);
    }

    /**
     * Histórico de alterações da demanda (somente registros da data atual).
     */
    public function historico($id)
    {
        $hoje = Carbon::today();

        $historico = DemandaHistory::with(['user:id_user,nome'])
            ->where('demanda_id', $id)
            ->whereDate('created_at', $hoje)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'status'     => $item->status,
                    'changed_by' => $item->user ? $item->user->nome : 'Sistema',
                    'created_at' => $item->created_at->format('d/m/Y'),
                ];
            });

        return response()->json($historico);
    }
}
