<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setores\RecebimentoItem;

class ConferenciaApiController extends Controller
{
    public function listarItens($recebimentoId)
    {
        $itens = RecebimentoItem::select(
                'id',
                'sku',
                'descricao',
                'quantidade',
                'qtd_conferida',
                'status',
                'observacao',
                'divergente',
                'avariado',
                'foto_avaria'
            )
            ->where('recebimento_id', $recebimentoId)
            ->paginate(50);
    
        // Converte 0/1 para true/false
        $itens->getCollection()->transform(function ($item) {
            $item->divergente = (bool) $item->divergente;
            $item->avariado = (bool) $item->avariado;
            return $item;
        });
    
        return response()->json($itens);
    }

    public function detalheItem($id)
    {
        return response()->json(RecebimentoItem::findOrFail($id));
    }

    public function salvarItem(Request $request, $id)
{
    $somenteVerificar = $request->boolean('somente_verificar', false);

    $item = RecebimentoItem::findOrFail($id);

    // Apenas verificar divergência, sem salvar
    if ($somenteVerificar) {
        $divergente = ($request->qtd_conferida != $item->quantidade);

        return response()->json([
            'success'    => true,
            'divergente' => $divergente ? true : false, // <-- boolean
            'mensagem'   => $divergente
                ? 'A conferência tem divergências, deseja continuar?'
                : 'Sem divergências'
        ]);
    }

    // Aqui já é para salvar de verdade
    $item->qtd_conferida = $request->qtd_conferida;
    $item->observacao    = $request->observacao ?? '';
    $item->avariado      = $request->avariado ?? false;

    // Foto de avaria (se houver)
    if ($item->avariado && $request->hasFile('foto_avaria')) {
        $path = $request->file('foto_avaria')->store('recebimento/avarias', 'public');
        $item->foto_avaria = $path;
    }

    // Definir divergência
    $item->divergente = ($item->qtd_conferida != $item->quantidade) ? 1 : 0;

    // Sempre marcar como conferido após salvar
    $item->status = 'conferido';

    $item->save();

    return response()->json([
        'success'    => true,
        'status'     => $item->status,
        'divergente' => (bool) $item->divergente, // <-- força boolean
        'mensagem'   => 'Item conferido com sucesso!'
    ]);
}



    public function fecharConferencia(Request $request, $id)
    {
        $request->validate([
            'foto_final' => 'required|image|max:5120',
            'assinatura' => 'required|boolean'
        ]);

        $path = $request->file('foto_final')->store('recebimento/fotos_finais', 'public');

        \DB::table('recebimentos')->where('id', $id)->update([
            'foto_fim_veiculo' => $path,
            'status' => 'Finalizado'
        ]);

        return response()->json(['success' => true, 'message' => 'Conferência finalizada']);
    }
    
    
}
