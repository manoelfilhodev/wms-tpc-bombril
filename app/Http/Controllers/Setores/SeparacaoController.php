<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setores\Separacao;
use App\Models\Setores\SeparacaoItem;
use App\Models\Setores\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class SeparacaoController extends Controller
{
    
    public function pular($id)
{
    $item = DB::table('_tb_separacao_itens')->where('id', $id)->first();

    if (!$item) {
        return back()->with('error', 'Item não encontrado.');
    }

    // Marcar o item como pulado (sem atualizar updated_at)
    DB::table('_tb_separacao_itens')
        ->where('id', $id)
        ->update([
            'conferido' => 2, // 2 = pulado
        ]);

    // (Opcional) Liberar a posição do cache, se tiver controle de posição
    if (isset($item->posicao_id)) {
        Cache::forget('posicao_ocupada_' . $item->posicao_id);
    }

    // Buscar o próximo item não conferido
    $proximo = DB::table('_tb_separacao_itens')
        ->where('pedido_id', $item->pedido_id)
        ->where('conferido', 0)
        ->orderBy('id')
        ->first();

    if ($proximo) {
        return redirect()->route('separacoes.separar_item', $proximo->id)
            ->with('success', 'Item pulado. Prosseguindo para o próximo.');
    } else {
        return redirect()->route('separacoes.andamento')
            ->with('success', 'Todos os itens disponíveis foram separados ou pulados.');
    }
}


    
    public function pendencias()
{
    $pendencias = session('pendencias_separacao', []);

    if (empty($pendencias)) {
        return back()->with('error', 'Nenhuma pendência encontrada.');
    }

    return view('setores.separacao.relatorios.pendencias', compact('pendencias'));
}
    

public function linha($id)
{
    $item = DB::table('_tb_separacao_itens')->where('id', $id)->first();
    $sku_id = DB::table('_tb_materiais')->where('sku', $item->sku)->value('id');

    $posicoes = DB::table('_tb_saldo_estoque as s')
        ->join('_tb_posicoes as p', 'p.id', '=', 's.posicao_id')
        ->where('s.sku_id', $sku_id)
        ->where('s.quantidade', '>=', $item->quantidade)
        ->where('s.unidade_id', Auth::user()->unidade_id)
        ->select('p.codigo_posicao', 'p.id')
        ->orderBy('p.codigo_posicao')
        ->get();

    $posicaoDisponivel = null;

    foreach ($posicoes as $pos) {
        $chave = 'posicao_ocupada_' . $pos->id;
        $ocupadoPor = Cache::get($chave);

        if (!$ocupadoPor || $ocupadoPor == Auth::id()) {
            $posicaoDisponivel = $pos;
            Cache::put($chave, Auth::id(), now()->addMinutes(5));
            break;
        }
    }

    return view('setores.separacao.linha.separar', [
        'item' => $item,
        'posicao' => $posicaoDisponivel
    ]);
}



public function liberarPosicao($posicaoId)
{
    Cache::forget('posicao_ocupada_' . $posicaoId);
    return response()->json(['status' => 'ok']);
}



    public function salvarLinhaManual(Request $request)
    {
        $sku = strtolower($request->input('sku'));
        $endereco = strtolower($request->input('endereco'));

        // Verifica se o produto existe
        $produtoExiste = DB::table('_tb_materiais')->where('sku', $sku)->exists();
        if (!$produtoExiste) {
            return back()->with('error', 'Produto nПлкo encontrado no sistema.');
        }

        $request->validate([
            'pedido' => 'required|string|max:50',
            'sku' => 'required|string|max:100',
            'quantidade' => 'required|integer|min:1',
            'endereco' => 'required|string|max:50',
        ]);

        Separacao::create([
            'pedido' => $request->pedido,
            'sku' => $sku,
            'quantidade' => $request->quantidade,
            'endereco' => $endereco,
            'observacoes' => $request->observacoes,
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
        ]);

        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Movimentacao de Separacao Kit',
            'dados' => '[SEPARACAO KIT] - ' . Auth::user()->nome .
                       ' realizou movimentacao do SKU ' . $sku .
                       ', quantidade ' . $request->quantidade .
                       ', no endereco ' . $endereco .
                       ', pedido ' . $request->pedido . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);

        return back()->with('success', 'Item separado com sucesso!');
    }

    
    public function listarEmAndamento()
{
    $separacoes = Pedido::where('status', 'em_separacao')->with('itensSeparacao')->get();
    return view('setores.separacao.separacoes.index', compact('separacoes'));
}

    
    public function verItensSeparacao($pedido_id)
{
    $pedido = Pedido::with('itensSeparacao')->findOrFail($pedido_id); // 'itensSeparacao' = relacionamento com _tb_separacao_itens

    return view('setores.separacao.pedidos.separacao_itens', compact('pedido'));

}

    public function iniciar($pedido_id)
{
    $pedido = Pedido::with('itens')->findOrFail($pedido_id);

    $pendencias = [];

    foreach ($pedido->itens as $item) {
        $sku_id = DB::table('_tb_materiais')->where('sku', $item->sku)->value('id');

        $temSaldo = DB::table('_tb_saldo_estoque')
            ->where('sku_id', $sku_id)
            ->where('quantidade', '>=', $item->quantidade)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->exists();

        if ($temSaldo) {
            SeparacaoItem::create([
                'pedido_id' => $pedido->id,
                'sku' => $item->sku,
                'quantidade' => $item->quantidade,
                'centro' => $item->centro,
                'fo' => $item->fo,
                'usuario_id' => auth()->id(),
                'unidade_id' => auth()->user()->unidade_id,
                'conferido' => false,
            ]);
        } else {
            $pendencias[] = [
                'sku' => $item->sku,
                'fo' => $item->fo,
                'quantidade' => $item->quantidade,
                'pedido' => $pedido->id,
            ];
        }
    }

    $pedido->status = 'em_separacao';
    $pedido->save();

    // Armazena as pendências temporariamente na sessão para exibir ou exportar
    session(['pendencias_separacao' => $pendencias]);

    return redirect()->route('separacoes.itens', $pedido->id)
        ->with('success', 'Oba! Separação iniciada com sucesso.')
        ->with('pendencias', count($pendencias) > 0);
}



    // public function listarEmAndamento()
    // {
    //     $separacoes = Separacao::with(['pedido', 'itens'])
    //         ->whereHas('pedido', function ($query) {
    //             $query->where('status', 'em_separacao');
    //         })
    //         ->orderByDesc('id')
    //         ->get();

    //     return view('setores.separacao.separacoes.index', compact('separacoes'));
    // }

    public function mostrarSeparacao($id)
    {
        $separacao = Separacao::with('itens', 'pedido')->findOrFail($id);
        return view('separacoes.detalhes', compact('separacao'));
    }

    public function index()
    {
        $pedidosPendentes = Pedido::where('status', 'pendente')->count();
        return view('setores.separacao.index', compact('pedidosPendentes'));
    }

    public function store(Request $request)
    {
        $pedido = strtolower($request->input('pedido'));
        $sku = strtolower($request->input('sku'));
        $endereco = strtolower($request->input('endereco'));

        $request->validate([
            'pedido' => 'required|string|max:50',
            'sku' => 'required|string|max:100',
            'quantidade' => 'required|integer|min:1',
            'endereco' => 'required|string|max:50',
        ]);

        $produtoExiste = \DB::table('_tb_materiais')->where('sku', $sku)->exists();
        if (!$produtoExiste) {
            return back()->with('error', 'Produto nПлкo encontrado no sistema.');
        }

        $posicaoExiste = \DB::table('_tb_posicoes')->where('codigo_posicao', $endereco)->exists();
        if (!$posicaoExiste) {
            return back()->with('error', 'PosiПлоПлкo de estoque nПлкo encontrada no sistema.');
        }

        Separacao::create([
            'pedido' => $pedido,
            'sku' => $sku,
            'quantidade' => $request->quantidade,
            'endereco' => $endereco,
            'observacoes' => $request->observacoes,
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
        ]);

        \DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Movimentacao de Separacao',
            'dados' => '[SEPARACAO] - ' . Auth::user()->nome .
                       ' realizou movimentacao do SKU ' . $sku .
                       ', quantidade ' . $request->quantidade .
                       ', no endereco ' . $request->endereco .
                       ', pedido ' . $request->pedido . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);

        return back()->with('success', 'Item separado com sucesso!');
    }
}
