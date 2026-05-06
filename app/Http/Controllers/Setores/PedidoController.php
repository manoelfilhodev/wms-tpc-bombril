<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setores\Pedido;
use App\Models\Setores\SeparacaoItem;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    private const FILTRO_DATA_SESSION_KEY = 'separacao_pedidos_filtro_data';
    
    public function index(Request $request)
    {
        if ($request->boolean('limpar_filtros')) {
            $request->session()->forget(self::FILTRO_DATA_SESSION_KEY);

            return redirect()->route('pedidos.index');
        }

        $dataFiltro = $this->resolverFiltroData($request);

        $pedidos = Pedido::with('itens')
            ->where('status', 'pendente')
            ->when($request->filled('numero'), function ($query) use ($request) {
                $numero = $request->string('numero')->trim()->value();

                $query->where('numero_pedido', 'like', "%{$numero}%");
            })
            ->when($request->filled('fo'), function ($query) use ($request) {
                $fo = $request->string('fo')->trim()->value();

                $query->whereHas('itens', function ($itensQuery) use ($fo) {
                    $itensQuery->where('fo', 'like', "%{$fo}%");
                });
            })
            ->when($dataFiltro, function ($query) use ($dataFiltro) {
                $query->whereDate('data_criacao', $dataFiltro);
            })
            ->orderByDesc('id')
            ->get();

        return view('setores.separacao.pedidos.index', compact('pedidos', 'dataFiltro'));
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

    private function resolverFiltroData(Request $request): ?string
    {
        if ($request->has('data')) {
            $data = $request->string('data')->trim()->value();

            if ($data === '') {
                $request->session()->forget(self::FILTRO_DATA_SESSION_KEY);

                return null;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) === 1) {
                $request->session()->put(self::FILTRO_DATA_SESSION_KEY, $data);

                return $data;
            }

            return null;
        }

        return $request->session()->get(self::FILTRO_DATA_SESSION_KEY);
    }
}
