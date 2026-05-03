<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setores\Recebimento;
use App\Models\Setores\RecebimentoItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecebimentoController extends Controller
{
    public function index()
    {
        return view('setores.recebimento.conferencia');
    }

    public function conferir(Request $request)
    {
        return back()->with('success', 'Item conferido com sucesso!');
    }

    public function painel()
    {
        $recebimentos = Recebimento::with('itens')
            ->orderBy('data_recebimento', 'desc')
            ->get();

        return view('setores.recebimento.painel', compact('recebimentos'));
    }

    public function detalharPainel($id)
    {
        $recebimento = DB::table('_tb_recebimento')->find($id);
        $itens = DB::table('_tb_recebimento_itens')
            ->where('recebimento_id', $id)
            ->get();

        $itensTotal = $itens->count();
        $itensConferidos = $itens->where('status', 'conferido')->count();
        $userTipo = Auth::user()->tipo ?? 'operador';

        return view('setores.recebimento.painel_detalhado', compact(
            'recebimento',
            'itens',
            'itensTotal',
            'itensConferidos',
            'userTipo'
        ));
    }

    public function create()
    {
        return view('setores.recebimento.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'motorista' => 'required|string|max:100',
            'placa' => 'required|string|max:20',
            'tipo' => 'required|string|max:50',
            'horario_janela' => 'required',
            'horario_chegada' => 'required',
            'doca' => 'required|string|max:20',
            'xml_nfe' => 'required|file|mimes:xml|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $xml = simplexml_load_file($request->file('xml_nfe')->getRealPath());
            if (!$xml) {
                throw new \Exception('Erro ao ler o XML da NF-e.');
            }

            $nf = (string) $xml->NFe->infNFe->ide->nNF;
            $fornecedor = (string) $xml->NFe->infNFe->emit->xNome;
            $transportadora = (string) ($xml->NFe->infNFe->transp->transporta->xNome ?? 'TRANSPORTADORA DESCONHECIDA');

            $nomeXML = 'nfe_' . time() . '.' . $request->file('xml_nfe')->getClientOriginalExtension();
            $request->file('xml_nfe')->move(public_path('xml_nfe'), $nomeXML);

            $recebimentoId = DB::table('_tb_recebimento')->insertGetId([
                'unidade_id'       => Auth::user()->unidade_id ?? 1,
                'nota_fiscal'      => $nf,
                'fornecedor'       => $fornecedor,
                'data_recebimento' => now()->toDateString(),
                'usuario_id'       => Auth::id(),
                'status'           => 'pendente',
                'transportadora'   => $transportadora,
                'motorista'        => $request->motorista,
                'placa'            => $request->placa,
                'tipo'             => $request->tipo,
                'horario_janela'   => $request->horario_janela,
                'horario_chegada'  => $request->horario_chegada,
                'doca'             => $request->doca,
                'xml_nfe'          => $nomeXML,
                'created_at'       => now(),
                'updated_at'       => now()
            ]);

            // Log
            DB::table('_tb_user_logs')->insert([
                'usuario_id' => Auth::id(),
                'unidade_id' => Auth::user()->unidade_id ?? 1,
                'acao'       => 'Início de Recebimento Documental',
                'dados'      => "[INÍCIO RECEBIMENTO] - " . Auth::user()->nome .
                    " iniciou o recebimento da NF {$nf}, tipo: {$request->tipo}, motorista: {$request->motorista}, placa: {$request->placa}, doca: {$request->doca}",
                'ip_address' => $request->ip(),
                'navegador'  => $request->header('User-Agent'),
                'created_at' => now()
            ]);

            // Itens
            foreach ($xml->NFe->infNFe->det as $item) {
                $sku = (string) $item->prod->cProd;
                $descricao = (string) $item->prod->xProd;
                $quantidade = (float) $item->prod->qCom;
                $ean = (string) $item->prod->cEAN;

                DB::table('_tb_recebimento_itens')->insert([
                    'recebimento_id' => $recebimentoId,
                    'sku'            => $sku,
                    'descricao'      => $descricao,
                    'quantidade'     => $quantidade,
                    'status'         => 'pendente',
                    'usuario_id'     => Auth::id(),
                    'unidade_id'     => Auth::user()->unidade_id ?? 1,
                    'created_at'     => now()
                ]);

                $material = DB::table('_tb_materiais')->where('sku', $sku)->first();
                if (!$material || !$material->paletizacao) {
                    Log::warning("SKU {$sku} não possui paletização cadastrada.");
                    continue;
                }

                $paletizacao = (int) $material->paletizacao;
                $lastro = (int) ($material->lastro ?? 0);
                $camada = (int) ($material->camada ?? 0);
                $totalEtiquetas = (int) ceil($quantidade / $paletizacao);

                for ($i = 1; $i <= $totalEtiquetas; $i++) {
                    $ua = $recebimentoId . date('YmdHisv') . mt_rand(100, 999);

                    // Ajuste de quantidade na última etiqueta (quebra de palete)
                    if ($i === $totalEtiquetas) {
                        // Quantidade restante é o total - já enviado nas etiquetas anteriores
                        $qtdEtiqueta = $quantidade - ($paletizacao * ($totalEtiquetas - 1));
                    } else {
                        $qtdEtiqueta = $paletizacao;
                    }

                    DB::table('_tb_recebimento_etiquetas')->insert([
                        'id_recebimento'  => $recebimentoId,
                        'sku'             => $sku,
                        'ean'             => $ean,
                        'descricao'       => $descricao,
                        'ua'              => $ua,
                        'lastro'          => $lastro,
                        'camada'          => $camada,
                        'paletizacao'     => $paletizacao,
                        'numero_etiqueta' => $i,
                        'total_etiquetas' => $totalEtiquetas,
                        'data_geracao'    => now(),
                        'quantidade'      => $qtdEtiqueta,
                        'created_at'      => now()
                    ]);

                    $zpl = $this->gerarZPLEtiqueta([
                        'sku'        => $sku,
                        'descricao'  => $descricao,
                        'ean'        => $ean,
                        'quantidade' => $qtdEtiqueta, // aqui também vai a quantidade ajustada
                        'ua'         => $ua
                    ]);

                    $dir = storage_path("app/etiquetas");
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    file_put_contents($dir . "/{$ua}.zpl", $zpl);
                }
            }

            DB::commit();
            return redirect()->route('setores.recebimento.painel')
                ->with('success', 'Recebimento e etiquetas gerados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao processar recebimento: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->withErrors(['error' => 'Erro ao processar o recebimento.']);
        }
    }

    private function gerarZPLEtiqueta($dados)
    {
        $sku = $dados['sku'];
        $descricao = substr($dados['descricao'], 0, 40);
        $ean = $dados['ean'];
        $quantidade = $dados['quantidade'];
        $ua = $dados['ua'];
        $dataHora = date('d/m/Y H:i');

        return "
^XA
^CF0,70
^FO40,40^FDDEXCO - CAJ^FS
^FO40,110^GB720,3,3^FS
^CF0,60
^FO40,140^FDSKU: {$sku}^FS
^CF0,35
^FO40,200^FD{$descricao}^FS
^FO40,240^FDQtd: {$quantidade}^FS
^FO40,280^FDUA: {$ua}^FS
^FO40,320^GB720,3,3^FS
^BY3,3,150
^FO120,350^BCN,150,N,N,N
^FD{$ean}^FS
^CF0,40
^FO250,520^FD{$ean}^FS
^FO40,560^GB720,3,3^FS
^CF0,25
^FO40,580^FDUA: {$ua}^FS
^FO40,610^FDImpresso: {$dataHora}^FS
^FO680,430^BQN,2,4
^FDLA,{$ua}^FS
^XZ
        ";
    }

    public function parseXml(Request $request)
{
    try {
        if ($request->hasFile('xml')) {
            $file = $request->file('xml');
            if (!$file->isValid()) {
                return response()->json(['ok' => false, 'message' => 'Arquivo XML inválido'], 400);
            }
            $content = file_get_contents($file->getRealPath());
        } else {
            $content = $request->getContent();
        }

        if (!$content || trim($content) === '') {
            return response()->json(['ok' => false, 'message' => 'Nenhum XML recebido'], 400);
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            $errors = array_map(fn($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();
            return response()->json(['ok' => false, 'message' => 'XML inválido', 'errors' => $errors], 400);
        }

        // Em muitas NFe o nó raiz é <nfeProc>, com a NFe dentro de <NFe><infNFe>...
        $infNFe = null;
        if (isset($xml->NFe->infNFe)) {
            $infNFe = $xml->NFe->infNFe;
        } elseif (isset($xml->infNFe)) {
            $infNFe = $xml->infNFe;
        } elseif (isset($xml->nfeProc->NFe->infNFe)) {
            $infNFe = $xml->nfeProc->NFe->infNFe;
        }

        if (!$infNFe) {
            return response()->json(['ok' => false, 'message' => 'Estrutura de NF-e não reconhecida'], 400);
        }

        $itens = [];
        if (isset($infNFe->det)) {
            foreach ($infNFe->det as $det) {
                $prod = $det->prod ?? null;
                if (!$prod) continue;

                // Campos comuns na NF-e
                $cEAN = trim((string)($prod->cEAN ?? ''));
                $cProd = trim((string)($prod->cProd ?? ''));
                $xProd = trim((string)($prod->xProd ?? ''));
                $uCom  = trim((string)($prod->uCom ?? 'UN'));
                $qCom  = (float)($prod->qCom ?? 0);

                // Defina sua regra de "sku": EAN se existir, senão cProd
                $sku = $cEAN && $cEAN !== 'SEM GTIN' ? $cEAN : $cProd;

                $itens[] = [
                    'sku'       => $sku,
                    'descricao' => $xProd,
                    'uom'       => $uCom ?: 'UN',
                    'qtd'       => $qCom > 0 ? $qCom : 0,
                    'status'    => $sku ? 'OK' : 'Pendente'
                ];
            }
        }

        if (empty($itens)) {
            return response()->json([
                'ok' => true,
                'message' => 'XML processado, mas nenhum item foi encontrado.',
                'itens' => [],
                'mensagens' => ['Verifique se o XML é uma NF-e válida e contém itens em infNFe/det.']
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'XML processado com sucesso',
            'itens' => $itens,
            'mensagens' => []
        ]);
    } catch (\Throwable $e) {
        \Log::error('Erro ao processar XML', ['ex' => $e]);
        return response()->json(['ok' => false, 'message' => 'Erro interno ao processar XML'], 500);
    }
}

    public function listar()
    {
        $recebimentos = DB::table('_tb_recebimento')
            ->select(
                'id',
                'nota_fiscal as nf',
                'fornecedor',
                'data_recebimento as data',
                'status',
                'placa'
            )
            ->orderByDesc('data_recebimento')
            ->get()
            ->map(function ($item) {
                $total = DB::table('_tb_recebimento_itens')->where('recebimento_id', $item->id)->count();
                $conferidos = DB::table('_tb_recebimento_itens')->where('recebimento_id', $item->id)->where('status', 'conferido')->count();
                $item->progresso = $total > 0 ? round($conferidos / $total, 2) : 0.0;
                return $item;
            });

        return response()->json($recebimentos);
    }
}
