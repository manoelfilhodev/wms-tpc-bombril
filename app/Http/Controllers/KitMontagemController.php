<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitMontagem;
use App\Models\ApontamentoKit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class KitMontagemController extends Controller
{
    /**
     * Tela principal da Central de Montagem de Kits
     */
    public function index()
    {
        $kits = KitMontagem::orderBy('data_montagem', 'desc')->get();
        return view('kit.index', compact('kits'));
       
    }

    /**
     * Formulário de apontamento de kit
     */
    public function create()
    {
        return view('kit.apontamento');
    }

    /**
     * Exibe novamente a view de apontamento (rota alternativa)
     */
    public function apontamento()
    {
        return view('kit.apontamento');
    }

    public function programar()
    {
        return view('kit.programar');
    }

public function storeProgramacao(Request $request)
{
    \Log::info("➡ Entrou no storeProgramacao do KitMontagemController");

    $request->validate([
        'codigo_material' => 'required|string',
        'quantidade_programada' => 'required|integer|min:1',
        'data_montagem' => 'required|date',
    ]);

    // cria a programação do kit
    $kit = KitMontagem::create([
        'codigo_material'       => $request->codigo_material,
        'quantidade_programada' => $request->quantidade_programada,
        'usuario_id'            => Auth::id(),
        'unidade_id'            => Auth::user()->unidade_id,
        'data_montagem'         => $request->data_montagem,
        'programado_por'        => Auth::id(),
        'programado_em'         => now(),
        'created_at'            => now(),
    ]);

    \Log::info("📦 Kit criado", ['kit_id' => $kit->id, 'codigo' => $kit->codigo_material]);

    // busca material
    $material = DB::table('_tb_materiais')
        ->where('sku', $kit->codigo_material)
        ->first();

    $descricao    = $material->descricao ?? '';
    $ean          = $material->ean ?? '';
    $lastro       = $material->lastro ?? '';
    $camada       = $material->camada ?? '';
    $paletizacao  = $material->paletizacao ?? 1;

    $qtdPorPalete = $paletizacao > 0 ? $paletizacao : 1;
    $qtdTotal = $kit->quantidade_programada;

    // cálculo de etiquetas
    $paletesCheios   = intdiv($qtdTotal, $qtdPorPalete);
    $sobra           = $qtdTotal % $qtdPorPalete;
    $totalEtiquetas  = $paletesCheios + ($sobra > 0 ? 1 : 0);

    // diretórios (iguais recebimento)
    $dirZpl = storage_path("app/etiquetas/kits/{$kit->id}");
    $dirPng = storage_path("app/etiquetas_png/kits/{$kit->id}");

    if (!is_dir($dirZpl)) {
        mkdir($dirZpl, 0775, true);
    }
    if (!is_dir($dirPng)) {
        mkdir($dirPng, 0775, true);
    }

    // loop de etiquetas
    // loop de etiquetas
for ($i = 1; $i <= $totalEtiquetas; $i++) {
    if ($i <= $paletesCheios) {
        $qtdEtiqueta = $qtdPorPalete; // cheio
    } else {
        $qtdEtiqueta = $sobra; // sobra/quebra
    }

    $kitId = $kit->id;
    $data  = now()->format('Ymd'); // 20250826

    // número sequencial formatado
    $numeroSequencia = str_pad($i, 3, '0', STR_PAD_LEFT);

    // gera UID
    $uid = "KIT{$kitId}-{$data}-{$numeroSequencia}";

    $apontamentoId = DB::table('_tb_apontamentos_kits')->insertGetId([
        'codigo_material' => $kit->codigo_material,
        'quantidade'      => $qtdEtiqueta,
        'data'            => $kit->data_montagem,
        'user_id'         => Auth::id(),
        'unidade'         => Auth::user()->unidade ?? 'default',
        'palete_uid'      => $uid,
        'status'          => 'GERADO',
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    \Log::info("📝 Apontamento criado", ['id' => $apontamentoId, 'qtd' => $qtdEtiqueta]);

    $agora = now()->format('d/m/Y H:i');

    // ==== gera o ZPL ====
    $zpl = "
^XA
^PW800
^LL800
^CF0,70
^FO40,40^FDPRODUCAO ^FS
^FO40,110^GB720,3,3^FS

^CF0,60
^FO40,140^FDSKU: " . strtoupper($material->sku) . "^FS
^CF0,35
^FO40,200^FD" . strtoupper($material->descricao) . "^FS
^FO40,240^FDQtd: {$qtdEtiqueta}^FS
^FO40,280^FDUA: {$uid}^FS
^FO500,280^FDEtiqueta {$i}/{$totalEtiquetas}^FS
^FO40,320^GB720,3,3^FS

^BY3,3,150
^FO120,350^BCN,150,N,N,N
^FD{$material->ean}^FS
^CF0,40
^FO250,520^FD{$material->ean}^FS

^FO40,560^GB720,3,3^FS

^CF0,25
^FO40,580^FDUA: {$uid}^FS
^FO40,610^FDImpresso: {$agora}^FS

^FO680,430^BQN,2,4
^FDLA,{$uid}^FS
^XZ";

    // caminhos
    $zplPath = "{$dirZpl}/kit_{$apontamentoId}.zpl";
    $pngPath = "{$dirPng}/kit_{$apontamentoId}.png";

    // salva ZPL no filesystem
    file_put_contents($zplPath, $zpl);
    \Log::info("✅ ZPL salvo", ['path' => $zplPath, 'exists' => file_exists($zplPath)]);

    // gera PNG via API
    $this->converterParaPng($zpl, $pngPath);
    \Log::info("✅ PNG salvo", ['path' => $pngPath, 'exists' => file_exists($pngPath)]);
}


    // registra log do usuário
    DB::table('_tb_user_logs')->insert([
        'usuario_id' => Auth::id(),
        'unidade_id' => Auth::user()->unidade_id,
        'acao'       => 'Programação de Kit',
        'dados'      => '[KIT] - ' . Auth::user()->nome .
            ' programou montagem do kit ' . $request->codigo_material .
            ', quantidade ' . $request->quantidade_programada .
            ', na data ' . $request->data_montagem .
            ' (paletização: ' . $paletizacao . ').',
        'ip_address' => request()->ip(),
        'navegador'  => request()->header('User-Agent'),
        'created_at' => now(),
    ]);

    return redirect()
    ->route('kits.etiquetas.visualizar', $kit->id)
    ->with('success', 'Programação registrada e etiquetas geradas automaticamente!');
}





    /**
     * Salva um novo lançamento de montagem de kit
     */
    public function store(Request $request)
{
    $user = Auth::user();

    // Salva apontamento na nova tabela
    DB::table('_tb_apontamentos_kits')->insert([
        'codigo_material' => $request->codigo_material,
        'quantidade' => $request->quantidade,
        'data' => $request->data_montagem,
        'user_id' => $user->id_user,
        'unidade' => $user->unidade_id,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Soma total produzido para o SKU na data específica
    $totalProduzido = DB::table('_tb_apontamentos_kits')
        ->where('codigo_material', $request->codigo_material)
        ->whereDate('data', $request->data_montagem)
        ->sum('quantidade');
    
    // Atualiza a tabela _tb_kit_montagem apenas para essa data
    DB::table('_tb_kit_montagem')
        ->where('codigo_material', $request->codigo_material)
        ->whereDate('data_montagem', $request->data_montagem)
        ->update([
            'quantidade_produzida' => $totalProduzido,
            'apontado_por' => $user->id_user,
            'apontado_em' => now()
        ]);

    // Log do apontamento
    DB::table('_tb_user_logs')->insert([
        'usuario_id' => $user->id_user,
        'unidade_id' => $user->unidade_id,
        'acao' => 'Apontamento de Kit',
        'dados' => '[KIT] - ' . $user->nome .
                ' apontou montagem do kit ' . $request->codigo_material .
                ', quantidade ' . $request->quantidade .
                ', na data ' . $request->data_montagem . '.',
        'ip_address' => request()->ip(),
        'navegador' => request()->header('User-Agent'),
        'created_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Apontamento registrado com sucesso!');
}

    /**
     * Busca SKUs dinamicamente para autocomplete
     */
    public function buscarSkus(Request $request)
    {
        $term = $request->input('term');

        $skus = DB::table('_tb_materiais')
            ->where('sku', 'LIKE', '%' . $term . '%')
            ->pluck('sku');

        return response()->json($skus);
    }

    /**
     * Retorna a descrição do SKU informado
     */
    public function buscarDescricao(Request $request)
    {
        $sku = $request->input('sku');

        $produto = DB::table('_tb_materiais')->where('sku', $sku)->first();

        if ($produto) {
            return response()->json([
                'descricao' => strtoupper($produto->descricao),
            ]);
        }

        return response()->json(['descricao' => null], 404);
    }
    
public function editProgramacao()
{
    $kitsHoje = KitMontagem::whereDate('data_montagem', today())->get();
    return view('kit.editar', compact('kitsHoje'));
}

public function updateProgramacao(Request $request, $id)
{
    $request->validate([
        'quantidade_programada' => 'required|integer|min:1',
        'data_montagem' => 'required|date',
    ]);

    $kit = KitMontagem::findOrFail($id);

    $oldQtd = $kit->quantidade_programada;
    $oldData = $kit->data_montagem;

    $kit->quantidade_programada = $request->quantidade_programada;
    $kit->data_montagem = $request->data_montagem;
    $kit->updated_at = now();
    $kit->save();

    // Salva no log de usuários
    DB::table('_tb_user_logs')->insert([
        'usuario_id' => Auth::id(),
        'unidade_id' => Auth::user()->unidade_id ?? null,
        'acao' => 'Alteração na Programação de Kits',
        'dados' => '[KIT] - ' . Auth::user()->nome .
           ' alterou programação do kit ' . $kit->codigo_material .
           ', de ' . $oldQtd . ' para ' . $request->quantidade_programada .
           ', na data ' . $request->data_montagem . '.',
        'ip_address' => $request->ip(),
        'navegador' => $request->header('User-Agent'),
        'created_at' => now(),
    ]);

    return redirect()->route('kit.programar')->with('success', 'Programação atualizada com sucesso.');
}

public function destroy($id)
{
    $kit = KitMontagem::findOrFail($id);

    DB::table('_tb_user_logs')->insert([
        'usuario_id' => Auth::id(),
        'unidade_id' => Auth::user()->unidade_id ?? null,
        'acao' => 'Exclusão de Programação de Kit',
        'dados' => '[KIT] - ' . Auth::user()->nome .
                   ' excluiu a programação do kit ' . $kit->codigo_material .
                   ', quantidade ' . $kit->quantidade_programada .
                   ', na data ' . $kit->data_montagem . '.',
        'ip_address' => request()->ip(),
        'navegador' => request()->header('User-Agent'),
        'created_at' => now(),
    ]);

    $kit->delete();

    return redirect()->route('kit.programar')->with('success', 'Programação excluída com sucesso.');
}


public function relatorio(Request $request)
{
    $query = KitMontagem::query();

    if ($request->filled('data_inicio')) {
        $query->whereDate('data_montagem', '>=', $request->data_inicio);
    }

    if ($request->filled('data_fim')) {
        $query->whereDate('data_montagem', '<=', $request->data_fim);
    }

    if ($request->filled('sku')) {
        $query->where('codigo_material', 'like', '%' . $request->sku . '%');
    }

    $kits = $query->orderBy('data_montagem', 'desc')->get();

    return view('kit.relatorio', compact('kits'));
}

public function exportarRelatorioPDF(Request $request)
{
    $query = KitMontagem::query();

    if ($request->filled('data_inicio')) {
        $query->whereDate('data_montagem', '>=', $request->data_inicio);
    }

    if ($request->filled('data_fim')) {
        $query->whereDate('data_montagem', '<=', $request->data_fim);
    }

    if ($request->filled('sku')) {
        $query->where('codigo_material', 'like', '%' . $request->sku . '%');
    }

    $kits = $query->orderBy('data_montagem', 'desc')->get();

    $pdf = Pdf::loadView('kit.relatorio_pdf', compact('kits'));
    $filename = 'kit_' . now()->format('Y-m-d_H-i') . '.pdf';
    return $pdf->download($filename);
}

public function exportarRelatorioExcel(Request $request)
{
    return Excel::download(new \App\Exports\KitMontagemExport($request), 'relatorio-kits.xlsx');
}
public function gerarEtiquetas(Request $request)
{
    $request->validate([
        'kit_id' => 'required|integer',
        'qtd_por_palete' => 'required|integer|min:1'
    ]);

    $kit = DB::table('_tb_kit_montagem')->where('id', $request->kit_id)->first();
    if (!$kit) {
        return back()->with('error', 'Kit não encontrado.');
    }

    $total = (int) ceil($kit->quantidade_programada / $request->qtd_por_palete);

    for ($i = 1; $i <= $total; $i++) {
        $uid = 'KP-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        DB::table('_tb_apontamentos_kits')->insert([
            'codigo_material' => $kit->codigo_material,
            'quantidade'      => $request->qtd_por_palete,
            'data'            => $kit->data_montagem,
            'user_id'         => auth()->id(),
            'unidade'         => auth()->user()->unidade ?? 'default',
            'palete_uid'      => $uid,
            'status'          => 'GERADO',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    return back()->with('success', 'Etiquetas geradas e salvas com sucesso!');
}


    /**
     * Apontar produção via bip (atualiza kit + apontamento)
     */
    public function apontarPorEtiqueta(Request $request)
    {
        $request->validate([
            'palete_uid' => 'required|string'
        ]);

        $apont = DB::table('_tb_apontamentos_kits')
            ->where('palete_uid', $request->palete_uid)
            ->first();

        if (!$apont) {
            return response()->json(['ok' => false, 'msg' => 'Etiqueta não encontrada.'], 404);
        }

        if ($apont->status === 'APONTADO') {
            return response()->json(['ok' => false, 'msg' => 'Etiqueta já apontada.'], 409);
        }

        if ($apont->status === 'CANCELADO') {
            return response()->json(['ok' => false, 'msg' => 'Etiqueta cancelada.'], 409);
        }

        DB::beginTransaction();
        try {
            // 1. atualiza quantidade produzida na montagem
            DB::table('_tb_kit_montagem')
                ->where('codigo_material', $apont->codigo_material)
                ->increment('quantidade_produzida', $apont->quantidade);

            // 2. marca apontamento como utilizado
            DB::table('_tb_apontamentos_kits')
                ->where('id', $apont->id)
                ->update([
                    'status' => 'APONTADO',
                    'updated_at' => now()
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Erro no apontamento: '.$e->getMessage()], 500);
        }

        return response()->json(['ok' => true, 'msg' => 'Apontado com sucesso!']);
    }

    /**
     * Builder ZPL para impressora térmica 100x80
     */
    private function buildEtiquetaZpl($kit, $uid, $qtd, $seq, $total): string
    {
        return "^XA
^PW800
^LL640
^CI28
^CF0,40
^FO30,30^FD KIT PRODUÇÃO ^FS
^CF0,28
^FO30,80^FD Material: {$kit->codigo_material} ^FS
^FO30,120^FD Qtd/Palete: {$qtd} ^FS
^FO30,160^FD Palete {$seq}/{$total} ^FS
^BY3,3,80
^FO30,220^BCN,80,Y,N,N
^FD{$uid}^FS
^CF0,28
^FO30,320^FD UID: {$uid} ^FS
^XZ";
    }
    
    public function scanner()
{
    return view('kit.scanner');
}

private function converterParaPng($zpl, $pngPath)
{
    // Impressora 8dpmm (203dpi), tamanho 100x80mm = 4x3.15 polegadas
    $url = 'http://api.labelary.com/v1/printers/8dpmm/labels/4x3.4/0/';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $zpl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: image/png']);
    $result = curl_exec($ch);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        file_put_contents($pngPath, $result);
        \Log::info("✅ PNG gerado pela Labelary", ['path' => $pngPath]);
    } else {
        \Log::error("❌ Erro ao gerar PNG", [
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'error'     => curl_error($ch)
        ]);
    }

    curl_close($ch);
}

public function apontar(Request $request)
{
    $request->validate([
        'palete_uid' => 'required|string'
    ]);

    $paleteUid = $request->palete_uid;

    // Buscar o apontamento
    $apontamento = DB::table('_tb_apontamentos_kits')
        ->where('palete_uid', $paleteUid)
        ->first();

    // Se não existir
    if (!$apontamento) {
        return back()->with('error', "❌ O código {$paleteUid} não existe no sistema!");
    }

    // Se já apontado
    if ($apontamento->status === 'APONTADO') {
        return back()->with('warning', "⚠️ O palete {$paleteUid} já foi apontado em " .
            \Carbon\Carbon::parse($apontamento->updated_at)->format('d/m/Y H:i'));
    }

    // Atualiza via query
    DB::table('_tb_apontamentos_kits')
        ->where('palete_uid', $paleteUid)
        ->update([
            'status'       => 'APONTADO',
            'apontado_por' => Auth::id(),
            'updated_at'   => now()
        ]);

    return back()->with('success', "✅ Palete {$paleteUid} apontado com sucesso!");
}




public function telaApontamento()
{
    $apontamentos = \App\Models\ApontamentoKit::with('apontadoPor')
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get();

    return view('kit.apontar', compact('apontamentos'));  // não redireciona
}

public function apiApontarPorEtiqueta(Request $request)
{
    $request->validate([
        'palete_uid' => 'required|string',
        'user_id'    => 'required|integer'
    ]);

    $paleteUid = $request->palete_uid;
    $userId    = $request->user_id;

    // Buscar o apontamento
    $apont = DB::table('_tb_apontamentos_kits')
        ->where('palete_uid', $paleteUid)
        ->first();

    if (!$apont) {
        return response()->json([
            'status'   => 'nao_encontrado',
            'mensagem' => '❌ Etiqueta não encontrada.'
        ], 404);
    }

    if ($apont->status === 'APONTADO') {
        return response()->json([
            'status'   => 'duplicado',
            'mensagem' => '⚠️ Essa etiqueta já foi apontada.'
        ], 409);
    }

    if ($apont->status === 'CANCELADO') {
        return response()->json([
            'status'   => 'cancelado',
            'mensagem' => '🚫 Essa etiqueta está cancelada.'
        ], 409);
    }

    // Verifica se o usuário existe
    $usuario = DB::table('_tb_usuarios')->where('id_user', $userId)->first();
    if (!$usuario) {
        return response()->json([
            'status'   => 'erro',
            'mensagem' => 'Código ou Usuário Inválido'
        ], 401);
    }

    DB::beginTransaction();
    try {
        // Atualiza quantidade produzida
        DB::table('_tb_kit_montagem')
            ->where('codigo_material', $apont->codigo_material)
            ->increment('quantidade_produzida', $apont->quantidade);

        // Marca apontamento
        DB::table('_tb_apontamentos_kits')
            ->where('id', $apont->id)
            ->update([
                'status'       => 'APONTADO',
                'apontado_por' => $userId,
                'updated_at'   => now()
            ]);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'   => 'erro',
            'mensagem' => 'Erro no apontamento: ' . $e->getMessage()
        ], 500);
    }

    return response()->json([
        'status'   => 'ok',
        'mensagem' => '✅ Palete apontado com sucesso!',
        'palete'   => $paleteUid,
        'usuario'  => strtoupper($usuario->nome)
    ]);
}



public function apiUltimosApontamentos()
{
    $dados = DB::table('_tb_apontamentos_kits as a')
    ->leftJoin('_tb_usuarios as u', 'a.apontado_por', '=', 'u.id_user')
    ->select(
        'a.palete_uid',
        'a.codigo_material',
        'a.quantidade',
        'a.status',
        'u.nome as apontado_por',
        'a.updated_at'
    )
    ->orderBy('a.updated_at', 'desc')
    ->limit(20)
    ->get()
    ->map(function ($item) {
        $item->apontado_por = strtoupper($item->apontado_por);
        return $item;
    });

    return response()->json($dados);
}

public function pendencias()
{
    // Busca etiquetas que ainda estão no status "GERADO" e foram criadas hoje
    $pendencias = \DB::table('_tb_apontamentos_kits')
        ->where('status', 'GERADO')
        ->whereDate('created_at', Carbon::today())
        ->get();

    return view('kit.pendencias', compact('pendencias'));
}



}
