<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setores\Pedido;
use App\Models\Setores\SeparacaoItem;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    
    public function index()
{
    $pedidos = \App\Models\Setores\Pedido::with('itens')
                ->where('status', 'pendente')
                ->orderByDesc('id')
                ->get();

    return view('setores.separacao.pedidos.index', compact('pedidos'));
}
    
    public function show($id)
{
    $pedido = Pedido::with('itens')->findOrFail($id);
    return view('setores.separacao.pedidos.show', compact('pedido'));
}


    public function create()
    {
        return view('setores.separacao.pedidos.create');
    }

    public function store(Request $request)
    {
        $dadosTexto = $request->input('itens_texto');

        $linhas = array_filter(explode("\n", $dadosTexto));
        $itens = [];

        foreach ($linhas as $linha) {
            $linha = trim($linha);
            
            // Ignora cabeçalho ou linha inválida
            if (stripos($linha, 'SKU') !== false || stripos($linha, 'CENTRO') !== false) {
                continue;
            }
        
            $partes = preg_split('/\s+/', $linha);
        
            if (count($partes) >= 4) {
                $itens[] = [
                    'centro' => strtoupper($partes[0]),
                    'fo' => strtoupper($partes[1]),
                    'sku' => strtoupper($partes[2]),
                    'quantidade' => (int)$partes[3]
                ];
            }
        }


        // Agrupa por SKU
        $consolidados = collect($itens)->groupBy('sku')->map(function ($grupo, $sku) {
            return [
                'sku' => $sku,
                'quantidade' => $grupo->sum('quantidade'),
                'centro' => $grupo->first()['centro'],
                'fo' => $grupo->first()['fo']
            ];
        })->values()->toArray();

        // Cria o pedido
        $pedido = Pedido::create([
            'numero_pedido' => 'PED' . time(),
            'unidade_id' => Auth::user()->unidade_id,
            'criado_por' => Auth::id(),
            'status' => 'pendente'
        ]);

        // Salva os itens vinculados ao pedido
        foreach ($consolidados as $item) {
            SeparacaoItem::create([
                'pedido_id' => $pedido->id,
                'separacao_id' => null,
                'sku' => $item['sku'],
                'quantidade' => $item['quantidade'],
                'centro' => $item['centro'],
                'fo' => $item['fo'],
                'usuario_id' => Auth::id(),
                'unidade_id' => Auth::user()->unidade_id,
                'conferido' => false
            ]);
        }

        return redirect()->route('pedidos.show', $pedido->id)->with('success', 'Pedido criado com sucesso!');
    }
}
