<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transferencia;
use App\Models\ApontamentoTransferencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TransferenciaController extends Controller
{
    /**
     * Tela principal da Central de Transferências
     */
    public function index()
    {
        $transferencias = Transferencia::orderBy('data_transferencia', 'desc')->get();
        return view('transferencia.index', compact('transferencias'));
    }

    /**
     * Formulário de apontamento de transferência
     */
    public function create()
    {
        return view('transferencia.apontamento');
    }

    public function apontamento()
    {
        return view('transferencia.apontamento');
    }

    public function programar()
    {
        return view('transferencia.programar');
    }

    public function storeProgramacao(Request $request)
    {
        $request->validate([
            'codigo_material' => 'required|string',
            'quantidade_programada' => 'required|integer|min:1',
            'data_transferencia' => 'required|date',
        ]);

        // cria a programação
        $transferencia = Transferencia::create([
            'codigo_material'       => $request->codigo_material,
            'quantidade_programada' => $request->quantidade_programada,
            'usuario_id'            => Auth::id(),
            'unidade_id'            => Auth::user()->unidade_id,
            'data_transferencia'    => $request->data_transferencia,
            'programado_por'        => Auth::id(),
            'programado_em'         => now(),
            'created_at'            => now(),
        ]);

        // gera etiquetas (igual ao kit)
        $material = DB::table('_tb_materiais')
            ->where('sku', $transferencia->codigo_material)
            ->first();

        $descricao    = $material->descricao ?? '';
        $ean          = $material->ean ?? '';
        $paletizacao  = $material->paletizacao ?? 1;

        $qtdPorPalete = $paletizacao > 0 ? $paletizacao : 1;
        $qtdTotal = $transferencia->quantidade_programada;

        $paletesCheios   = intdiv($qtdTotal, $qtdPorPalete);
        $sobra           = $qtdTotal % $qtdPorPalete;
        $totalEtiquetas  = $paletesCheios + ($sobra > 0 ? 1 : 0);

        for ($i = 1; $i <= $totalEtiquetas; $i++) {
            $qtdEtiqueta = $i <= $paletesCheios ? $qtdPorPalete : $sobra;
            $uid = "TRF{$transferencia->id}-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT);

            DB::table('_tb_apontamentos_transferencia')->insert([
                'codigo_material' => $transferencia->codigo_material,
                'quantidade'      => $qtdEtiqueta,
                'data'            => $transferencia->data_transferencia,
                'user_id'         => Auth::id(),
                'unidade'         => Auth::user()->unidade ?? 'default',
                'palete_uid'      => $uid,
                'status'          => 'GERADO',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()
            ->route('transferencia.index')
            ->with('success', 'Programação registrada e etiquetas geradas automaticamente!');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        DB::table('_tb_apontamentos_transferencia')->insert([
            'codigo_material' => $request->codigo_material,
            'quantidade'      => $request->quantidade,
            'data'            => $request->data_transferencia,
            'user_id'         => $user->id_user,
            'unidade'         => $user->unidade_id,
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

        $totalApontado = DB::table('_tb_apontamentos_transferencia')
            ->where('codigo_material', $request->codigo_material)
            ->whereDate('data', $request->data_transferencia)
            ->sum('quantidade');

        DB::table('_tb_transferencias')
            ->where('codigo_material', $request->codigo_material)
            ->whereDate('data_transferencia', $request->data_transferencia)
            ->update([
                'quantidade_apontada' => $totalApontado,
                'apontado_por'        => $user->id_user,
                'apontado_em'         => now()
            ]);

        return redirect()->back()->with('success', 'Apontamento de transferência registrado com sucesso!');
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

        return $produto
            ? response()->json(['descricao' => strtoupper($produto->descricao)])
            : response()->json(['descricao' => null], 404);
    }

    public function editProgramacao()
    {
        $transferenciasHoje = Transferencia::whereDate('data_transferencia', today())->get();
        return view('transferencia.editar', compact('transferenciasHoje'));
    }

    public function updateProgramacao(Request $request, $id)
    {
        $request->validate([
            'quantidade_programada' => 'required|integer|min:1',
            'data_transferencia'    => 'required|date',
        ]);

        $trf = Transferencia::findOrFail($id);
        $trf->quantidade_programada = $request->quantidade_programada;
        $trf->data_transferencia    = $request->data_transferencia;
        $trf->updated_at            = now();
        $trf->save();

        return redirect()->route('transferencia.programar')->with('success', 'Programação atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $trf = Transferencia::findOrFail($id);
        $trf->delete();

        return redirect()->route('transferencia.programar')->with('success', 'Programação de transferência excluída.');
    }

    public function relatorio(Request $request)
{
    $query = DB::table('_tb_transferencias as t')
        ->select(
            't.id',
            't.codigo_material',
            't.quantidade_programada',
            't.data_transferencia',
            DB::raw('COALESCE(SUM(a.quantidade),0) as quantidade_apontada')
        )
        ->leftJoin('_tb_apontamentos_transferencia as a', function($join){
            $join->on('t.codigo_material','=','a.codigo_material')
                 ->on('t.data_transferencia','=','a.data');
        })
        ->where('a.status','APONTADO')
        ->groupBy('t.id','t.codigo_material','t.quantidade_programada','t.data_transferencia');

    if ($request->filled('data_inicio')) {
        $query->whereDate('t.data_transferencia', '>=', $request->data_inicio);
    }
    if ($request->filled('data_fim')) {
        $query->whereDate('t.data_transferencia', '<=', $request->data_fim);
    }
    if ($request->filled('sku')) {
        $query->where('t.codigo_material', 'like', '%' . $request->sku . '%');
    }

    $transferencias = $query->orderBy('t.data_transferencia','desc')->get();

    return view('transferencia.relatorio', compact('transferencias'));
}


    public function exportarRelatorioPDF(Request $request)
    {
        $query = Transferencia::query();

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_transferencia', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_transferencia', '<=', $request->data_fim);
        }
        if ($request->filled('sku')) {
            $query->where('codigo_material', 'like', '%' . $request->sku . '%');
        }

        $transferencias = $query->orderBy('data_transferencia', 'desc')->get();

        $pdf = Pdf::loadView('transferencia.relatorio_pdf', compact('transferencias'));
        $filename = 'transferencia_' . now()->format('Y-m-d_H-i') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportarRelatorioExcel(Request $request)
    {
        return Excel::download(new \App\Exports\TransferenciaExport($request), 'relatorio-transferencias.xlsx');
    }

    public function pendencias()
    {
        $pendencias = DB::table('_tb_apontamentos_transferencia')
            ->where('status', 'GERADO')
            ->whereDate('created_at', Carbon::today())
            ->get();

        return view('transferencia.pendencias', compact('pendencias'));
    }
    
public function telaApontamento()
{
    $hoje = Carbon::today()->toDateString();

    // Conta apenas os registros com status APONTADO no dia de hoje (com base no updated_at)
    $qtdPaletesHoje = DB::table('_tb_apontamentos_transferencia')
        ->whereDate('updated_at', $hoje)
        ->where('status', 'APONTADO')
        ->count();

    // Resumo de SKUs apontados hoje
    $skuResumo = DB::table('_tb_apontamentos_transferencia')
        ->select(
            'codigo_material as sku',
            DB::raw('SUM(quantidade) as total_quantidade'),
            DB::raw('COUNT(DISTINCT palete_uid) as total_etiquetas')
        )
        ->whereDate('updated_at', $hoje)
        ->where('status', 'APONTADO')
        ->groupBy('codigo_material')
        ->orderBy('codigo_material')
        ->get();

    // Últimos apontamentos detalhados
    $apontamentos = ApontamentoTransferencia::with('apontadoPor')
        ->orderByDesc('updated_at')
        ->limit(20)
        ->get();
        
    $totalGeral = [
    'quantidade' => $skuResumo->sum('total_quantidade'),
    'etiquetas'  => $skuResumo->sum('total_etiquetas'),
];

    return view('transferencia.apontamento', compact('apontamentos', 'qtdPaletesHoje', 'skuResumo', 'totalGeral'));

}



public function apontar(Request $request)
{
    $request->validate([
        'palete_uid' => 'required|string'
    ]);

    $paleteUid = $request->palete_uid;

    // Buscar o apontamento
    $apontamento = \DB::table('_tb_apontamentos_transferencia')
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
    \DB::table('_tb_apontamentos_transferencia')
        ->where('palete_uid', $paleteUid)
        ->update([
            'status'       => 'APONTADO',
            'apontado_por' => \Auth::id(),
            'updated_at'   => now()
        ]);

    return back()->with('success', "✅ Palete {$paleteUid} apontado com sucesso!");
}


public function apontamentosHoje()
{
    $hoje = Carbon::today()->toDateString();

    $qtdPaletesHoje = DB::table('_tb_apontamentos_transferencia')
        ->whereDate('data', $hoje)
        ->where('status', 'APONTADO')
        ->count();

    $apontamentos = ApontamentoTransferencia::with('apontadoPor')
        ->orderByDesc('updated_at')
        ->limit(20)
        ->get();

    return view('transferencia.apontamento', compact('apontamentos', 'qtdPaletesHoje'));
}




}
