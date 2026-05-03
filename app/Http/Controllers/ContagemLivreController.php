<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContagemLivreController extends Controller
{
    public function form()
    {
        return view('contagem.contagem-livre');
    }

public function salvar(Request $request)
{
    DB::table('_tb_contagem_livre')->insert([
        'ficha' => $request->ficha,
        'sku' => $request->sku,
        'quantidade' => $request->quantidade,
        'contado_por' => Auth::id()
    ]);

    $mensagem = "Contagem registrada com sucesso para o SKU {$request->sku}, ficha {$request->ficha}, com {$request->quantidade} unidades.";

    $contagens = DB::table('_tb_contagem_livre as c')
        ->join('_tb_usuarios as u', 'c.contado_por', '=', 'u.id_user')
        ->select('c.ficha', 'c.sku', 'c.quantidade', 'u.nome as usuario', 'c.data_hora')
        ->orderByDesc('c.data_hora')
        ->limit(50)
        ->get();

    return view('contagem.contagem-livre', compact('mensagem', 'contagens'));
}

public function buscarDescricaoApi(Request $request)
{
    $ean = $request->get('ean');

    if (empty($ean)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Informe o EAN para busca.'
        ], 422);
    }

    // Busca na tabela _tb_materiais
    $produto = DB::table('_tb_materiais')
        ->where('ean', $ean)
        ->first();

    if (!$produto) {
        return response()->json([
            'status' => 'error',
            'message' => 'Produto não encontrado para o EAN informado.'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Produto encontrado com sucesso.',
        'data' => [
            'material_id' => $produto->id,
            'ean'         => $produto->ean,
            'sku'         => mb_strtoupper($produto->sku),
            'descricao'   => mb_strtoupper($produto->descricao),
        ]
    ]);
}



  public function store(Request $request)
{
    $request->validate([
        'sku'        => 'required|string',
        'ficha'      => 'nullable|string|max:100',
        'quantidade' => 'required|integer|min:1',
        'contado_por' => 'required|integer'
    ]);

    $id = DB::table('_tb_contagem_livre')->insertGetId([
        'sku'         => $request->sku,        
        'ficha'       => $request->ficha,
        'quantidade'  => $request->quantidade,
        'contado_por'  => $request->contado_por,
        'data_hora'  => Carbon::now()
        
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Contagem salva com sucesso!',
        'data'    => [
            'id'        => $id,
            'sku'       => $request->sku,
            'ficha'     => $request->ficha,
            'quantidade'=> $request->quantidade,
            'contado_por'=> $request->contado_por
        ]
    ]);
}





public function listar()
{
    $contagens = DB::table('_tb_contagem_livre as c')
        ->join('_tb_usuarios as u', 'c.contado_por', '=', 'u.id_user')
        ->select('c.ficha', 'c.sku', 'c.quantidade', 'u.nome as usuario', 'c.data_hora')
        ->orderByDesc('c.data_hora')
        ->get();

    return view('contagem.listar-contagens', compact('contagens'));
}




}
