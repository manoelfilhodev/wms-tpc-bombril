<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventarioExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\LogHelper;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;


class InventarioCiclicoController extends Controller
{

    public function contar($id_inventario, $itemId)
    {
        $item = DB::table('_tb_inventario_itens')->where('id', $itemId)->first();

        if (!$item) {
            return redirect()->route('inventario.importar')->with('error', 'Item não encontrado.');
        }

        return view('inventario.contar', compact('item', 'id_inventario'));
    }
    
    public function salvarContagem(Request $request, $id_inventario, $itemId)
    {
        $item = DB::table('_tb_inventario_itens')->where('id', $itemId)->first();
    
        if (!$item) {
            return back()->with('error', 'Item não encontrado.');
        }
    
        // Proteção contra dupla contagem
        if ($item->quantidade_fisica !== null && $item->contado_por !== Auth::id()) {
            return back()->with('error', 'Este item já foi contado por outro usuário.');
        }
    
        $qtd = (int) $request->input('quantidade_fisica');
        $pos = $request->input('posicao') ?? $item->posicao;
    
        $tipo = 'nenhum';
        if ($qtd > $item->quantidade_sistema) $tipo = 'sobra';
        elseif ($qtd < $item->quantidade_sistema) $tipo = 'falta';
    
        DB::table('_tb_inventario_itens')->where('id', $itemId)->update([
            'quantidade_fisica' => $qtd,
            'posicao' => $pos,
            'tipo_ajuste' => $tipo,
            'necessita_ajuste' => $tipo !== 'nenhum' ? 1 : 0,
            'ajustado' => 0,
            'contado_por' => Auth::id(), // salva quem contou
            'updated_at' => now()
        ]);
        
        LogHelper::registrar(
            'INVENTÁRIO',
            '[CONTAGEM] - Usuário ' . Auth::user()->nome .
            ' contou o SKU "' . $item->sku . '" na posição "' . $pos .
            '" com quantidade física: ' . $qtd .
            ' (sistema: ' . $item->quantidade_sistema . '). Ajuste: ' . $tipo
        );
    
        // Próximo item não contado
        $proximo = DB::table('_tb_inventario_itens')
            ->where('id_inventario', $id_inventario)
            ->whereNull('quantidade_fisica')
            ->orderBy('posicao')
            ->first();
    
        if ($proximo) {
            return redirect()->route('inventario.contar', [$id_inventario, $proximo->id]);
        }
    
        return redirect()->route('inventario.validacao', $id_inventario)
            ->with('success', 'Contagem finalizada!');
            
            $total = DB::table('_tb_inventario_itens')->where('id_inventario', $id_inventario)->count();
            $contados = DB::table('_tb_inventario_itens')->where('id_inventario', $id_inventario)->whereNotNull('quantidade_fisica')->count();
            
            if ($total > 0 && $contados == $total) {
                DB::table('_tb_inventario_ciclico')
                    ->where('id', $id_inventario)
                    ->update(['status' => 'contado']);
            }
    }

    public function validacao($id_inventario)
    {
        $itens = DB::table('_tb_inventario_itens')
            ->where('id_inventario', $id_inventario)
            ->orderBy('posicao')
            ->get();

        return view('inventario.validacao', compact('itens', 'id_inventario'));
    }
    
    public function iniciarContagem($id_inventario)
    {
    $primeiroItem = DB::table('_tb_inventario_itens')
        ->where('id_inventario', $id_inventario)
        ->whereNull('quantidade_fisica')
        ->orderBy('posicao')
        ->first();

    if (!$primeiroItem) {
        return redirect()->route('inventario.validacao', $id_inventario)
            ->with('info', 'Todos os itens já foram contados.');
    }

    return redirect()->route('inventario.contar', [$id_inventario, $primeiroItem->id]);
}

    public function gerarInventario(Request $request)
{
    $linhas = explode(PHP_EOL, trim($request->input('lista_skus')));
    $itens = [];

    foreach ($linhas as $linha) {
        $partes = preg_split('/\\t+/', trim($linha));

        if (count($partes) >= 1) {
            $sku = trim($partes[0]);

            $material = DB::table('_tb_materiais')->where('sku', $sku)->first();

            if ($material) {
                $posicoes = DB::table('_tb_saldo_estoque')
                    ->leftJoin('_tb_posicoes', '_tb_saldo_estoque.posicao_id', '=', '_tb_posicoes.id')
                    ->where('_tb_saldo_estoque.sku_id', $material->id)
                    ->select(
                        DB::raw("COALESCE(_tb_posicoes.codigo_posicao, NULL) as posicao"),
                        '_tb_saldo_estoque.quantidade'
                    )
                    ->get();

                if ($posicoes->isEmpty()) {
                    // SKU sem posição nem saldo cadastrado
                    $itens[] = [
                        'sku' => $material->sku,
                        'descricao' => $material->descricao,
                        'posicao' => NULL,
                        'quantidade_sistema' => 0,
                    ];
                } else {
                    foreach ($posicoes as $pos) {
                        $itens[] = [
                            'sku' => $material->sku,
                            'descricao' => $material->descricao,
                            'posicao' => $pos->posicao,
                            'quantidade_sistema' => $pos->quantidade,
                        ];
                    }
                }
            }
        }
    }

    if (empty($itens)) {
        return back()->with('error', 'Nenhum SKU válido foi encontrado.');
    }

    $requisicaoId = DB::table('_tb_inventario_ciclico')->insertGetId([
        'cod_requisicao' => 'INV-' . strtoupper(Str::random(6)),
        'data_requisicao' => now(),
        'status' => 'contando',
        'usuario_criador' => Auth::user()->nome,
        'created_at' => now()
    ]);

    foreach ($itens as $item) {
        DB::table('_tb_inventario_itens')->insert([
            'id_inventario' => $requisicaoId,
            'sku' => $item['sku'],
            'descricao' => $item['descricao'],
            'posicao' => $item['posicao'],
            'quantidade_sistema' => $item['quantidade_sistema'],
            'created_at' => now()
        ]);
    }

    LogHelper::registrar(
        'INVENTÁRIO',
        '[CRIAÇÃO] - Usuário ' . Auth::user()->nome .
        ' criou o inventário ID ' . $requisicaoId .
        ' com ' . count($itens) . ' SKUs/posições.'
    );

    return redirect()->route('inventario.importar')
        ->with('success', 'Inventário gerado com sucesso!');
}



    public function importar()
    {
        return view('inventario.importar');
    }
    
    public function pular($id_inventario, $itemId)
    {
        // Apenas encontra o próximo sem alterar o atual
        $proximo = DB::table('_tb_inventario_itens')
            ->where('id_inventario', $id_inventario)
            ->whereNull('quantidade_fisica')
            ->where('id', '!=', $itemId)
            ->orderBy('posicao')
            ->first();
    
        if ($proximo) {
            return redirect()->route('inventario.contar', [$id_inventario, $proximo->id]);
        }
    
        return redirect()->route('inventario.validacao', $id_inventario)
            ->with('info', 'Todos os itens foram contados ou pulados.');
    }
    
    public function resumo($id)
    {
        $itens = DB::table('_tb_inventario_itens')->where('id_inventario', $id)->get();
    
        $total = $itens->count();
        $contados = $itens->whereNotNull('quantidade_fisica')->count();
        $faltantes = $total - $contados;
    
        return view('inventario.resumo', compact('itens', 'total', 'contados', 'faltantes', 'id'));
    }
    
    public function efetivar($id)
    {
        // Atualiza status da requisição
        DB::table('_tb_inventario_ciclico')
            ->where('id', $id)
            ->update(['status' => 'concluida']);
    
        // Recupera os itens contados no inventário
        $itens = DB::table('_tb_inventario_itens')
            ->where('id_inventario', $id)
            ->whereNotNull('quantidade_fisica')
            ->get();
    
        foreach ($itens as $item) {
            // Busca ID do SKU
            $skuId = DB::table('_tb_materiais')->where('sku', $item->sku)->value('id');
    
            if (!$skuId) continue;
    
            // Remove saldo antigo do SKU na posição (se houver)
            DB::table('_tb_saldo_estoque')
                ->where('sku_id', $skuId)
                ->where('posicao_id', function ($query) use ($item) {
                    $query->select('id')
                        ->from('_tb_posicoes')
                        ->where('codigo_posicao', $item->posicao)
                        ->limit(1);
                })
                ->delete();
    
            // Recupera ou cria a posição
            $posicaoId = DB::table('_tb_posicoes')
                ->where('codigo_posicao', $item->posicao)
                ->value('id');
    
            if ($posicaoId) {
                // Insere novo saldo com base na contagem
                DB::table('_tb_saldo_estoque')->insert([
                    'sku_id' => $skuId,
                    'posicao_id' => $posicaoId,
                    'quantidade' => $item->quantidade_fisica,
                    'updated_at' => now(),
                ]);
            }
        }
    
        // Log da efetivação
        LogHelper::registrar(
            'Efetivação de Inventário',
            '[EFETIVAÇÃO] - Usuário ' . Auth::user()->nome .
            ' efetivou o inventário ID ' . $id . ' e atualizou os saldos físicos.'
        );
    
        return redirect()->route('inventario.requisicoes')
            ->with('success', 'Inventário efetivado e saldos atualizados com sucesso!');
    }

    public function saldos(Request $request)
    {
        $saldos = DB::table('_tb_saldo_estoque as s')
            ->join('_tb_materiais as m', 's.sku_id', '=', 'm.id')
            ->join('_tb_posicoes as p', 's.posicao_id', '=', 'p.id')
            ->select('m.sku', 'm.descricao', 'p.codigo_posicao', 's.quantidade')
            ->when($request->sku, function ($query) use ($request) {
                return $query->where('m.sku', 'like', '%' . $request->sku . '%');
            })
            ->when($request->descricao, function ($query) use ($request) {
                return $query->where('m.descricao', 'like', '%' . $request->descricao . '%');
            })
            ->when($request->posicao, function ($query) use ($request) {
                return $query->where('p.codigo_posicao', 'like', '%' . $request->posicao . '%');
            })
            ->orderBy('p.codigo_posicao')
            ->get();
    
        return view('inventario.saldos', compact('saldos'));
    }



    public function posicoes(Request $request)
    {
        $posicoes = DB::table('_tb_posicoes as p')
            ->leftJoin('_tb_unidades as u', 'p.unidade_id', '=', 'u.id')
            ->select('p.*', 'u.nome as unidade_nome')
            ->when($request->codigo, fn($q) => $q->where('p.codigo_posicao', 'like', '%' . $request->codigo . '%'))
            ->when($request->status !== null, fn($q) => $q->where('p.status', $request->status))
            ->orderBy('p.codigo_posicao')
            ->get();
    
        // Se quiser acessar $p->unidade->nome na view:
        $posicoes->transform(function ($p) {
            $p->unidade = (object)['nome' => $p->unidade_nome];
            return $p;
        });
    
        return view('inventario.posicoes', compact('posicoes'));
    }

    public function salvarPosicao(Request $request)
    {
        $request->validate([
            'codigo_posicao' => 'required|string|max:100'
        ]);
    
        DB::table('_tb_posicoes')->insert([
            'codigo_posicao' => $request->codigo_posicao,
            'status' => 1,
            'unidade_id' => Auth::user()->unidade_id ?? null, // ← adiciona isso
            'created_at' => now()
        ]);
    
        return back()->with('success', 'Posição criada com sucesso.');
    }

    
    
    public function listarRequisicoes()
    {
        $status = request('status');
    
        $inventarios = DB::table('_tb_inventario_ciclico')
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->get();
    
        foreach ($inventarios as $inv) {
            $total = DB::table('_tb_inventario_itens')
                ->where('id_inventario', $inv->id)
                ->count();
    
            $contados = DB::table('_tb_inventario_itens')
                ->where('id_inventario', $inv->id)
                ->whereNotNull('quantidade_fisica')
                ->count();
    
            $inv->total_itens = $total;
            $inv->contados = $contados;
            $inv->progresso = $total > 0 ? round(($contados / $total) * 100) : 0;
        }
    
        return view('inventario.requisicoes', compact('inventarios'));
    }
    
    public function exportarPdf($id)
    {
        $itens = DB::table('_tb_inventario_itens')->where('id_inventario', $id)->get();
    
        $total = $itens->count();
        $contados = $itens->whereNotNull('quantidade_fisica')->count();
        $faltantes = $total - $contados;
    
        $codigo = DB::table('_tb_inventario_ciclico')->where('id', $id)->value('cod_requisicao');
        $timestamp = now()->format('Ymd_His');
        $nomeArquivo = $codigo . '_' . $timestamp . '.pdf';
    
        $pdf = \PDF::loadView('inventario.pdf', compact('itens', 'total', 'contados', 'faltantes', 'id'));
    
        return $pdf->download($nomeArquivo);
    }
    
    public function exportarExcel($id)
    {
        $codigo = DB::table('_tb_inventario_ciclico')->where('id', $id)->value('cod_requisicao');
        $timestamp = now()->format('Ymd_His');
        $nomeArquivo = $codigo . '_' . $timestamp . '.xlsx';
    
        return Excel::download(new InventarioExport($id), $nomeArquivo);
    }
    

public function gerarFichasDiretas(Request $request)
{
    $linhas = explode(PHP_EOL, trim($request->input('lista')));
    $codReferencia = $request->input('referencia_existente');

    // Se não selecionou uma lista existente, cria uma nova
    if (empty($codReferencia)) {
        $codReferencia = 'FICHAS-' . now()->format('Ymd-His');
    }

    // Descobre a última ordem usada nessa referência
    $ultimaOrdem = DB::table('_tb_inventario_fichas')
        ->where('cod_referencia', $codReferencia)
        ->max('ordem') ?? 0;

    $itens = [];

    foreach ($linhas as $index => $linha) {
        $partes = preg_split('/\t+/', trim($linha)); // separa por TAB

        if (count($partes) >= 2) {
            $posicao = strtoupper(trim($partes[0]));
            $sku = trim($partes[1]);
            $deposito = $partes[2] ?? null;

            // Tenta pegar descrição, se não existir, deixa em branco
            $material = DB::table('_tb_materiais')->where('sku', $sku)->first();
            $descricao = $material->descricao ?? '';

            // Verifica se já existe esse SKU + posição na mesma lista
            $jaExiste = DB::table('_tb_inventario_fichas')
                ->where('cod_referencia', $codReferencia)
                ->where('sku', $sku)
                ->where('posicao', $posicao)
                ->exists();

            if ($jaExiste) {
                continue; // evita duplicatas
            }

            DB::table('_tb_inventario_fichas')->insert([
                'cod_referencia' => $codReferencia,
                'sku' => $sku,
                'descricao' => $descricao,
                'posicao' => $posicao,
                'deposito' => $deposito,
                'ordem' => ++$ultimaOrdem,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $itens[] = (object)[
                'sku' => $sku,
                'descricao' => $descricao,
                'posicao' => $posicao,
                'deposito' => $deposito,
            ];
        }
    }

    if (empty($itens)) {
        return back()->with('error', 'Nenhuma nova ficha foi inserida (pode ser duplicada ou inválida).');
    }

    return redirect()->route('inventario.fichas.reimprimir', ['cod' => $codReferencia])
        ->with('success', count($itens) . ' fichas adicionadas com sucesso à lista ' . $codReferencia);
}



public function reimprimirFichas($cod)
{
    $itens = DB::table('_tb_inventario_fichas')
        ->where('cod_referencia', $cod)
        ->orderBy('ordem')
        ->get();

    if ($itens->isEmpty()) {
        return back()->with('error', 'Nenhuma ficha encontrada com esse código.');
    }

    return view('inventario.fichas_contagem', compact('itens'));
}


public function formImportarFichas()
{
    $referencias = DB::table('_tb_inventario_fichas')
        ->select('cod_referencia', DB::raw('COUNT(*) as total'))
        ->groupBy('cod_referencia')
        ->orderByDesc('cod_referencia')
        ->get();

    return view('inventario.fichas_importar', compact('referencias'));
}









}