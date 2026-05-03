<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Setores\Armazenagem;

class ArmazenagemController extends Controller
{
    public function index()
    {
        return view('setores.armazenagem.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:100',
            'quantidade' => 'required|integer|min:1',
            'endereco' => 'required|string|max:50',
        ]);

        $sku = $request->input('sku');
        $quantidade = $request->input('quantidade');
        $endereco = $request->input('endereco');
        $observacoes = $request->input('observacoes');

        $usuario_id = Auth::id();
        $unidade_id = Auth::user()->unidade_id ?? 1;

        // Verifica SKU
        $material = DB::table('_tb_materiais')->where('sku', $sku)->first();
        if (!$material) {
            return back()->with('error', 'Produto não encontrado no sistema.');
        }

        // Verifica posição
        $posicao = DB::table('_tb_posicoes')->where('codigo_posicao', $endereco)->first();
        if (!$posicao) {
            return back()->with('error', 'Endereço informado não está cadastrado no sistema.');
        }

        // Registra movimentação de armazenagem (para rastreio)
        $armazenagem = Armazenagem::create([
            'sku' => $sku,
            'quantidade' => $quantidade,
            'endereco' => $endereco,
            'observacoes' => $observacoes,
            'usuario_id' => $usuario_id,
            'unidade_id' => $unidade_id,
        ]);

        // Atualiza ou insere saldo na posição
        $registroSaldo = DB::table('_tb_saldo_estoque')
            ->where('sku_id', $material->id)
            ->where('posicao_id', $posicao->id)
            ->where('unidade_id', $unidade_id)
            ->first();

        if ($registroSaldo) {
            DB::table('_tb_saldo_estoque')
                ->where('id', $registroSaldo->id)
                ->update([
                    'quantidade' => $registroSaldo->quantidade + $quantidade,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('_tb_saldo_estoque')->insert([
                'sku_id' => $material->id,
                'posicao_id' => $posicao->id,
                'quantidade' => $quantidade,
                'unidade_id' => $unidade_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Registra movimentação no histórico
        DB::table('_tb_movimentacoes_estoque')->insert([
            'sku_id' => $material->id,
            'posicao_id' => $posicao->id,
            'unidade_id' => $unidade_id,
            'tipo' => 'ENTRADA',
            'quantidade' => $quantidade,
            'origem' => 'ARMAZENAGEM',
            'referencia_id' => $armazenagem->id,
            'usuario_id' => $usuario_id,
            'observacoes' => $observacoes,
            'created_at' => now(),
        ]);

        // Log do usuário
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => $usuario_id,
            'unidade_id' => $unidade_id,
            'acao' => 'Movimentacao de Armazenagem',
            'dados' => '[ARMAZENAGEM] - ' . Auth::user()->nome .
                       ' armazenou SKU ' . $sku .
                       ', quantidade ' . $quantidade .
                       ', no endereço ' . $endereco . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Produto armazenado com sucesso!');
    }

    public function buscarSkus(Request $request)
    {
        $term = $request->input('term');

        $skus = DB::table('_tb_materiais')
            ->where('sku', 'LIKE', '%' . $term . '%')
            ->pluck('sku');

        return response()->json($skus);
    }

    public function buscarDescricao(Request $request)
    {
        $sku = $request->input('sku');

        $produto = DB::table('_tb_materiais')->where('sku', $sku)->first();

        if ($produto) {
            return response()->json([
                'descricao' => strtoupper($produto->descricao),
            ]);
        }

        return response()->json([
            'descricao' => null
        ], 404);
    }

    public function buscarPosicoes(Request $request)
    {
        $term = $request->input('term');

        $posicoes = DB::table('_tb_posicoes')
            ->where('codigo_posicao', 'LIKE', '%' . $term . '%')
            ->where('status', 'ativa')
            ->pluck('codigo_posicao');

        return response()->json($posicoes);
    }
    
    public function buscarDescricaoApi(Request $request)
{
    $ean = $request->query('sku'); // o app envia "sku", mas no banco é EAN

    $produto = DB::table('_tb_materiais')->where('ean', $ean)->first();

    if ($produto) {
        return response()->json([
            'descricao' => strtoupper($produto->descricao),
            'estoque'   => $produto->quantidade_estoque,
            'ean'       => $produto->ean,
            'sku'       => $produto->sku,
        ]);
    }

    return response()->json(['descricao' => null], 404);
}

public function storeApi(Request $request)
{
    $request->validate([
        'sku' => 'required|string|max:100',
        'quantidade' => 'required|integer|min:1',
        'endereco' => 'required|string|max:50',
        'usuario_id' => 'required|integer' // Agora obrigatório no corpo
    ]);

    $sku = $request->input('sku');
    $quantidade = $request->input('quantidade');
    $endereco = $request->input('endereco');
    $observacoes = $request->input('observacoes');
    $usuario_id = $request->input('usuario_id'); // vem do body da API
    $unidade_id = Auth::user()->unidade_id ?? 1;

    // Verifica SKU (pelo EAN ou SKU)
    $material = DB::table('_tb_materiais')
        ->where('sku', $sku)
        ->orWhere('ean', $sku)
        ->first();

    if (!$material) {
        return response()->json(['error' => 'Produto não encontrado no sistema.'], 404);
    }

    // Verifica posição
    $posicao = DB::table('_tb_posicoes')->where('codigo_posicao', $endereco)->first();
    if (!$posicao) {
        return response()->json(['error' => 'Endereço informado não está cadastrado no sistema.'], 404);
    }

    // Atualiza ou insere saldo na posição
    $registroSaldo = DB::table('_tb_saldo_estoque')
        ->where('sku_id', $material->id)
        ->where('posicao_id', $posicao->id)
        ->where('unidade_id', $unidade_id)
        ->first();

    if ($registroSaldo) {
        DB::table('_tb_saldo_estoque')
            ->where('id', $registroSaldo->id)
            ->update([
                'quantidade' => $registroSaldo->quantidade + $quantidade,
                'updated_at' => now(),
            ]);
    } else {
        DB::table('_tb_saldo_estoque')->insert([
            'sku_id' => $material->id,
            'posicao_id' => $posicao->id,
            'quantidade' => $quantidade,
            'unidade_id' => $unidade_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Registra movimentação no histórico
    DB::table('_tb_movimentacoes_estoque')->insert([
        'sku_id' => $material->id,
        'posicao_id' => $posicao->id,
        'unidade_id' => $unidade_id,
        'tipo' => 'ENTRADA',
        'quantidade' => $quantidade,
        'origem' => 'ARMAZENAGEM_API',
        'referencia_id' => null,
        'usuario_id' => $usuario_id,
        'observacoes' => $observacoes,
        'created_at' => now(),
    ]);

    // Log do usuário
    DB::table('_tb_user_logs')->insert([
        'usuario_id' => $usuario_id,
        'unidade_id' => $unidade_id,
        'acao' => 'Movimentacao de Armazenagem API',
        'dados' => '[ARMAZENAGEM API] - SKU ' . $sku .
                   ', quantidade ' . $quantidade .
                   ', no endereço ' . $endereco,
        'ip_address' => $request->ip(),
        'navegador' => $request->header('User-Agent'),
        'created_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Produto armazenado com sucesso!',
        'sku' => $sku,
        'quantidade' => $quantidade,
        'endereco' => $endereco,
        'usuario_id' => $usuario_id
    ]);
}




}
