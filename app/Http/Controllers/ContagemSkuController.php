<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContagemSkuController extends Controller
{
    /**
     * Exibe o formulário para colar os SKUs e gerar a listagem.
     */
    public function formulario()
    {
        return view('contagem.formulario');
    }

    /**
     * Processa os SKUs recebidos e salva no banco.
     */
    public function salvar(Request $request)
    {
        $linhas = explode(PHP_EOL, $request->input('lista_skus'));
        $idLista = (string) Str::uuid();

        $dadosInseridos = [];

        foreach ($linhas as $linha) {
            $partes = preg_split('/\t+/', trim($linha));

            if (count($partes) >= 3) {
                $dadosInseridos[] = [
                    'id_lista' => $idLista,
                    'material' => trim($partes[0]),
                    'centro' => trim($partes[1]),
                    'descricao' => trim($partes[2]),
                    'quantidade' => 0,
                    'criado_por' => Auth::id(),
                    'criado_em' => now(),
                ];
            }
        }

        if (count($dadosInseridos)) {
            DB::table('_tb_listagem_skus_contagem')->insert($dadosInseridos);
            return redirect()->route('contagem.lista', ['id_lista' => $idLista])
                             ->with('success', 'Listagem criada com sucesso!');
        } else {
            return back()->with('error', 'Nenhum dado válido foi detectado.');
        }
    }

    /**
     * Exibe a listagem da contagem com input para quantidades.
     */
    public function exibir($id_lista)
    {
        $dados = DB::table('_tb_listagem_skus_contagem')
            ->where('id_lista', $id_lista)
            ->get();

        return view('contagem.listagem', compact('dados', 'id_lista'));
    }

    /**
     * Atualiza as quantidades contadas.
     */
    public function salvarContagem(Request $request, $inventarioId, $itemId)
{
    $request->validate([
        'quantidade_fisica' => 'required|numeric',
        'posicao' => 'nullable|string|max:100'
    ]);

    DB::table('_tb_inventario_itens')
        ->where('id', $itemId)
        ->update([
            'quantidade_fisica' => $request->quantidade_fisica,
            'posicao' => $request->posicao ?? null,
            'contado_por' => auth()->id(), // Salva o usuário que contou
            'updated_at' => now()
        ]);

    return redirect()->route('contar.inventario', ['idInventario' => $inventarioId])
        ->with('success', 'Contagem salva com sucesso!');
}


    
public function listarItensInventario($idInventario)
{
    // Busca todos os itens do inventário informado
    $itens = DB::table('_tb_inventario_itens as i')
        ->leftJoin('_tb_usuarios as u', 'i.contado_por', '=', 'u.id_user')
        ->where('i.id_inventario', $idInventario)
        ->select('i.*', 'u.nome as nome_usuario')
        ->get();

    // Itens com posição válida (mínimo 2 caracteres, sem espaços)
    $itensComPosicao = $itens->filter(function ($item) {
        return isset($item->posicao) && strlen(trim($item->posicao)) > 1;
    })->sortBy(function ($item) {
        return strtoupper($item->posicao);
    });

    // Itens sem posição (null, vazios ou só traços)
    $itensSemPosicao = $itens->filter(function ($item) {
        return !isset($item->posicao) || strlen(trim($item->posicao)) <= 1;
    });

    // Envia os dados para a view
    return view('inventario.contar', [
        'itensComPosicao' => $itensComPosicao,
        'itensSemPosicao' => $itensSemPosicao,
        'idInventario' => $idInventario
    ]);
}


public function formContagem($inventarioId, $itemId)
{
    $item = DB::table('_tb_inventario_itens')->where('id', $itemId)->first();

    if (!$item) {
        return redirect()->back()->with('error', 'Item não encontrado');
    }

    return view('inventario.contar_item', compact('item', 'inventarioId'));
}



    
}
