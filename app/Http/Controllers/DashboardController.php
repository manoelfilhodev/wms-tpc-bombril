<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
{
    if (auth()->user()?->tipo === 'operador') {
        return redirect()->route('painel.operador');
    }

    if (Schema::hasTable('_tb_demanda')) {
        return redirect()->route('demandas.dashboardOperacional');
    }

    $hoje = Carbon::today();

    $totais             = $this->dashboardService->getTotaisGerais();
    $contagens          = $this->dashboardService->getContagensDoDia($hoje);
    $graficoSetores     = $this->dashboardService->getVolumePorSetor($hoje);
    $volume7Dias        = $this->dashboardService->getVolume7Dias();
    $dadosMensais       = $this->dashboardService->getDadosMensaisPorDia();
    $notificacoes       = $this->dashboardService->getNotificacoesPendentes();
    $ocupacoes          = $this->dashboardService->getOcupacaoRelativaPorPosicao();
    $ocupacao_cd        = $this->dashboardService->getOcupacaoTotalDoCD();
    $rankingOperadores  = $this->dashboardService->getRankingOperadores();
    $resumoDia          = $this->dashboardService->getResumoDoDia();
    $kitsHoje           = $this->dashboardService->getProducaoDeKitsHoje();
    $acuracidadeMensal  = $this->dashboardService->getAcuracidadeMensal();
    $resumoSkusHoje     = $this->dashboardService->getResumoSkusHoje();
    $progressoContagem  = $this->dashboardService->getProgressoContagemHoje();
    $produtividadeHora  = $this->dashboardService->getProdutividadeHoraHoje();
    $demandasHoje       = $this->dashboardService->getDemandasHoje();
    $statusContagemGeral = $this->dashboardService->getStatusContagemGeral();

    return view('dashboard', compact(
        'totais',
        'contagens',
        'graficoSetores',
        'volume7Dias',
        'dadosMensais',
        'notificacoes',
        'ocupacoes',
        'ocupacao_cd',
        'rankingOperadores',
        'resumoDia',
        'kitsHoje',
        'acuracidadeMensal',
        'resumoSkusHoje',
        'progressoContagem',
        'produtividadeHora',
        'demandasHoje',
        'statusContagemGeral',
    ));
}


    public function marcarNotificacaoLida($id)
    {
        $this->dashboardService->marcarNotificacaoComoLida($id);
        return redirect()->back()->with('success', 'Notificação marcada como lida.');
    }

    public function exportarResumoDiaPDF()
    {
        $dados = $this->dashboardService->getResumoDoDia();
        $dataHoje = Carbon::today()->format('d-m-Y');

        $pdf = Pdf::loadView('dashboard.resumo_pdf', compact('dados', 'dataHoje'));
        return $pdf->download("resumo-dia-{$dataHoje}.pdf");
    }
    
    public function projecaoProdutividade()
{
    $dados = $this->dashboardService->getProjecaoProdutividade();
    return response()->json($dados);
}

}
