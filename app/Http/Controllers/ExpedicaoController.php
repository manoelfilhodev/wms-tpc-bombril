<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ChartHelper;



class ExpedicaoController extends Controller
{
    private function formatMinutes($minutos)
    {
        if (is_null($minutos)) return '-';
        $h = floor($minutos / 60);
        $m = $minutos % 60;
        return $h > 0 ? "{$h}h {$m}min" : "{$m}min";
    }

    public function dashboard()
    {
        $hoje = Carbon::today();
        $inicio = $hoje->copy()->subDays(7); // últimos 7 dias
        $mesAtual = $hoje->format('Y-m');

        /**
         * ========================
         * KPIs MENSAIS
         * ========================
         */
        $temposMensal = DB::table('_tb_demanda_status_history as h')
            ->selectRaw('
                DATE_FORMAT(h.updated_at, "%Y-%m") as mes,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "SEPARANDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status IN ("A_CONFERIR","CONFERINDO"))
                    )
                ) as tmsep,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CONFERINDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CONFERIDO")
                    )
                ) as tmconf,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CARREGANDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CARREGADO")
                    )
                ) as tmcarr
            ')
            ->whereRaw('DATE_FORMAT(h.updated_at, "%Y-%m") = ?', [$mesAtual])
            ->groupBy('mes')
            ->first();

        if ($temposMensal) {
            $tmsep  = $temposMensal->tmsep ?? 0;
            $tmconf = $temposMensal->tmconf ?? 0;
            $tmcarr = $temposMensal->tmcarr ?? 0;

            // TMGP = soma dos tempos médios das etapas
            $temposMensal->tmgp = $tmsep + $tmconf + $tmcarr;

            $temposMensal->tmsep  = $this->formatMinutes($tmsep);
            $temposMensal->tmconf = $this->formatMinutes($tmconf);
            $temposMensal->tmcarr = $this->formatMinutes($tmcarr);
            $temposMensal->tmgp   = $this->formatMinutes($temposMensal->tmgp);
        }

        /**
         * ========================
         * KPIs DE VOLUME MENSAL
         * ========================
         */
        $volumeMensal = DB::table('_tb_demanda')
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as mes,
                SUM(quantidade) as qtd_total,
                SUM(peso) as peso_total,
                COUNT(id) as qtd_demandas
            ')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$mesAtual])
            ->groupBy('mes')
            ->first();

        /**
         * ========================
         * GRÁFICOS DIÁRIOS (últimos 7 dias)
         * ========================
         */
        $volume = DB::table('_tb_demanda')
            ->selectRaw('DATE(created_at) as dia, SUM(quantidade) as qtd, SUM(peso) as peso')
            ->whereBetween(DB::raw('DATE(created_at)'), [$inicio, $hoje])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $fosLiberadas = DB::table('_tb_demanda_status_history')
            ->selectRaw('DATE(updated_at) as dia, COUNT(DISTINCT demanda_id) as total')
            ->where('status', 'LIBERADO')
            ->whereBetween(DB::raw('DATE(updated_at)'), [$inicio, $hoje])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $tempos = DB::table('_tb_demanda_status_history as h')
            ->selectRaw('
                DATE(h.updated_at) as dia,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "SEPARANDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status IN ("A_CONFERIR","CONFERINDO"))
                    )
                ) as tmsep,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CONFERINDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CONFERIDO")
                    )
                ) as tmconf,
                AVG(
                    TIMESTAMPDIFF(MINUTE,
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CARREGANDO"),
                        (SELECT MIN(created_at) FROM _tb_demanda_status_history WHERE demanda_id = h.demanda_id AND status = "CARREGADO")
                    )
                ) as tmcarr
            ')
            ->whereBetween(DB::raw('DATE(h.updated_at)'), [$inicio, $hoje])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(function ($item) {
                $tmsep  = $item->tmsep ?? 0;
                $tmconf = $item->tmconf ?? 0;
                $tmcarr = $item->tmcarr ?? 0;

                // TMGP = soma dos tempos médios das etapas
                $item->tmgp = $tmsep + $tmconf + $tmcarr;

                $item->tmsep  = $this->formatMinutes($tmsep);
                $item->tmconf = $this->formatMinutes($tmconf);
                $item->tmcarr = $this->formatMinutes($tmcarr);
                $item->tmgp   = $this->formatMinutes($item->tmgp);

                return $item;
            });
            
            /**
 * ========================
 * VOLUME POR TRANSPORTADORA (mensal)
 * ========================
 */
$volumeTransportadora = DB::table('_tb_demanda')
    ->selectRaw('transportadora, SUM(quantidade) as qtd_total, SUM(peso) as peso_total')
    ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$mesAtual])
    ->groupBy('transportadora')
    ->orderByDesc('qtd_total')
    ->get();

/**
 * ========================
 * VOLUME POR MOTORISTA (mensal)
 * ========================
 */
$volumeMotorista = DB::table('_tb_demanda')
    ->selectRaw('motorista, SUM(quantidade) as qtd_total, SUM(peso) as peso_total')
    ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$mesAtual])
    ->groupBy('motorista')
    ->orderByDesc('qtd_total')
    ->get();

        return view('expedicao.dashboard', compact(
    'temposMensal',
    'volumeMensal',
    'mesAtual',
    'volume',
    'fosLiberadas',
    'tempos',
    'volumeTransportadora',
    'volumeMotorista'
));
    }    
    
public function exportarPdf()
{
    $hoje = Carbon::today();
    $data_hoje = $hoje->format('d/m/Y H:i');
    $data_referencia = $hoje->format('Y-m');
    $titulo_principal = "Relatório de Expedição";
    $topo = public_path('images/topo_expedicao.jpg');

    // Função auxiliar para formatar minutos -> "Xh Ymin"
    $formatarMinutos = function ($minutos) {
        if (!$minutos) return '0min';
        $h = floor($minutos / 60);
        $m = $minutos % 60;
        return ($h ? $h . 'h ' : '') . $m . 'min';
    };

    /**
     * 1) Demandas por Status
     */
    $status = DB::table('_tb_demanda')
        ->select('status', DB::raw('COUNT(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

    $chartStatus = [
        'type' => 'doughnut',
        'data' => [
            'labels' => $status->keys(),
            'datasets' => [[
                'data' => $status->values(),
                'backgroundColor' => ['#0d6efd','#ffc107','#198754','#dc3545','#6610f2','#20c997'],
            ]]
        ],
        'options' => [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'datalabels' => ['color' => '#000','font' => ['weight' => 'bold']]
            ]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 2) Ranking de Transportadoras
     */
    $transportadoras = DB::table('_tb_demanda')
        ->select('transportadora', DB::raw('COUNT(*) as total'))
        ->groupBy('transportadora')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    $chartTransportadoras = [
        'type' => 'bar',
        'data' => [
            'labels' => $transportadoras->pluck('transportadora'),
            'datasets' => [[
                'label' => 'Veículos',
                'data' => $transportadoras->pluck('total'),
                'backgroundColor' => '#3b82f6'
            ]]
        ],
        'options' => [
            'indexAxis' => 'y',
            'scales' => ['x' => ['beginAtZero' => true]],
            'plugins' => ['datalabels' => ['align' => 'right','anchor' => 'end','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 3) Ranking de Motoristas
     */
    $motoristas = DB::table('_tb_demanda')
        ->select('motorista', DB::raw('COUNT(*) as total'))
        ->groupBy('motorista')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    $chartMotoristas = [
        'type' => 'bar',
        'data' => [
            'labels' => $motoristas->pluck('motorista'),
            'datasets' => [[
                'label' => 'Viagens',
                'data' => $motoristas->pluck('total'),
                'backgroundColor' => '#10b981'
            ]]
        ],
        'options' => [
            'indexAxis' => 'y',
            'scales' => ['x' => ['beginAtZero' => true]],
            'plugins' => ['datalabels' => ['anchor' => 'end','align' => 'right','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 4) Volume diário (últimos 7 dias)
     */
    $inicio = $hoje->copy()->subDays(6);
    $volume = DB::table('_tb_demanda')
        ->selectRaw('DATE(created_at) as dia, SUM(quantidade) as qtd')
        ->whereBetween(DB::raw('DATE(created_at)'), [$inicio, $hoje])
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

    $chartVolume = [
        'type' => 'bar',
        'data' => [
            'labels' => $volume->pluck('dia'),
            'datasets' => [[
                'label' => 'Peças',
                'data' => $volume->pluck('qtd'),
                'borderColor' => '#f97316','fill' => false,
            ]]
        ],
        'options' => [
            'scales' => ['y' => ['beginAtZero' => true]],
            'plugins' => ['datalabels' => ['align' => 'top','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 5) FOs Liberadas por Dia
     */
    $fos = DB::table('_tb_demanda')
        ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

    $chartFOs = [
        'type' => 'bar',
        'data' => [
            'labels' => $fos->pluck('dia'),
            'datasets' => [[
                'label' => 'FOs Liberadas',
                'data' => $fos->pluck('total'),
                'borderColor' => '#ff5722','fill' => false
            ]]
        ],
        'options' => [
            'plugins' => ['datalabels' => ['align' => 'top','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

/**
 * 6) Tempos médios por etapa (mesma lógica do dashboard)
 */
$tempos = DB::table('_tb_demanda_status_history as h')
    ->selectRaw('
        DATE(h.updated_at) as dia,
        AVG(
            TIMESTAMPDIFF(MINUTE,
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status = "SEPARANDO"),
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status IN ("A_CONFERIR","CONFERINDO"))
            )
        ) as tmsep,
        AVG(
            TIMESTAMPDIFF(MINUTE,
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status = "CONFERINDO"),
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status = "CONFERIDO"))
        ) as tmconf,
        AVG(
            TIMESTAMPDIFF(MINUTE,
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status = "CARREGANDO"),
                (SELECT MIN(created_at) 
                 FROM _tb_demanda_status_history 
                 WHERE demanda_id = h.demanda_id 
                   AND status = "CARREGADO"))
        ) as tmcarr
    ')
    ->whereBetween(DB::raw('DATE(h.updated_at)'), [$hoje->copy()->subDays(6), $hoje])
    ->groupBy('dia')
    ->orderBy('dia')
    ->get();

$temposMedios = $tempos->map(function ($item) {
    $sep  = $item->tmsep ?? 0;
    $conf = $item->tmconf ?? 0;
    $carr = $item->tmcarr ?? 0;

    return [
        'dia'    => $item->dia,
        'tmsep'  => round($sep, 2),
        'tmconf' => round($conf, 2),
        'tmcarr' => round($carr, 2),
        'tmgp'   => round($sep + $conf + $carr, 2), // 👈 soma das etapas
    ];
});





    // Converter minutos para horas (decimais)
    $temposMediosHoras = $temposMedios->map(function ($i) {
        return [
            'dia'    => $i['dia'],
            'tmsep'  => $i['tmsep']  !== null ? round($i['tmsep']  / 60, 2) : null,
            'tmconf' => $i['tmconf'] !== null ? round($i['tmconf'] / 60, 2) : null,
            'tmcarr' => $i['tmcarr'] !== null ? round($i['tmcarr'] / 60, 2) : null,
            'tmgp'   => $i['tmgp']   !== null ? round($i['tmgp']   / 60, 2) : null,
        ];
    });

    $chartTempos = [
        'type' => 'line',
        'data' => [
            'labels' => $temposMediosHoras->pluck('dia'),
            'datasets' => [
                ['label' => 'Separação (h)','data' => $temposMediosHoras->pluck('tmsep'),'borderColor' => '#0d6efd','fill' => false],
                ['label' => 'Conferência (h)','data' => $temposMediosHoras->pluck('tmconf'),'borderColor' => '#ffc107','fill' => false],
                ['label' => 'Carregamento (h)','data' => $temposMediosHoras->pluck('tmcarr'),'borderColor' => '#198754','fill' => false],
                ['label' => 'Geral (h)','data' => $temposMediosHoras->pluck('tmgp'),'borderColor' => '#dc3545','fill' => false],
            ]
        ],
        'options' => [
            'scales' => [
                'y' => ['beginAtZero' => true,'title' => ['display' => true,'text' => 'Horas']]
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'datalabels' => [
                    'align' => 'top','color' => '#000','font' => ['weight' => 'bold'],
                    'formatter' => 'function(v){ return v != null ? v.toFixed(2) + "h" : "" }'
                ]
            ]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 7) Volume por Tipo de Carga
     */
    $tipos = DB::table('_tb_demanda')
        ->select('tipo', DB::raw('SUM(quantidade) as total'))
        ->groupBy('tipo')
        ->get();

    $chartTipos = [
        'type' => 'bar',
        'data' => [
            'labels' => $tipos->pluck('tipo'),
            'datasets' => [[
                'label' => 'Quantidade',
                'data' => $tipos->pluck('total'),
                'backgroundColor' => '#6366f1'
            ]]]
        ,
        'options' => [
            'scales' => ['y' => ['beginAtZero' => true]],
            'plugins' => ['datalabels' => ['anchor' => 'end','align' => 'top','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * 8) Peso total por Transportadora
     */
    $pesoTransportadoras = DB::table('_tb_demanda')
        ->select('transportadora', DB::raw('SUM(peso) as total'))
        ->groupBy('transportadora')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    $chartPesoTransportadoras = [
        'type' => 'bar',
        'data' => [
            'labels' => $pesoTransportadoras->pluck('transportadora'),
            'datasets' => [[
                'label' => 'Peso (kg)',
                'data' => $pesoTransportadoras->pluck('total'),
                'backgroundColor' => '#f43f5e'
            ]]]
        ,
        'options' => [
            'indexAxis' => 'y',
            'scales' => ['x' => ['beginAtZero' => true]],
            'plugins' => ['datalabels' => ['anchor' => 'end','align' => 'top','color' => '#000','font' => ['weight' => 'bold']]]
        ],
        'plugins' => ['datalabels']
    ];

    /**
     * KPIs (query única)
     */
    $totais = DB::table('_tb_demanda')
        ->selectRaw('SUM(quantidade) as volume, SUM(peso) as peso, COUNT(*) as demandas')
        ->first();

    $kpis = [
        'tmsep'   => $formatarMinutos(round($temposMedios->avg('tmsep'))),
        'tmconf'  => $formatarMinutos(round($temposMedios->avg('tmconf'))),
        'tmcarr'  => $formatarMinutos(round($temposMedios->avg('tmcarr'))),
        'tmgp'    => $formatarMinutos(round($temposMedios->avg('tmgp'))),
        'volume'  => number_format($totais->volume ?? 0, 0, ',', '.'),
        'peso'    => number_format($totais->peso ?? 0, 0, ',', '.') . ' kg',
        'demandas'=> $totais->demandas ?? 0,
    ];

    $graficos = [
        ['tipo' => 'kpis','titulo' => 'KPIs Gerais da Expedição','dados' => $kpis],
        ['titulo' => 'Demandas por Status','url' => ChartHelper::gerarGraficoBase64($chartStatus),'descricao' => 'Distribuição das demandas por status no período.'],
        ['titulo' => 'Ranking de Transportadoras','url' => ChartHelper::gerarGraficoBase64($chartTransportadoras),'descricao' => 'Top 10 transportadoras com maior número de veículos.'],
        ['titulo' => 'Ranking de Motoristas','url' => ChartHelper::gerarGraficoBase64($chartMotoristas),'descricao' => 'Top 10 motoristas com maior número de viagens.'],
        ['titulo' => 'Volume Diário (Últimos 7 dias)','url' => ChartHelper::gerarGraficoBase64($chartVolume),'descricao' => 'Volume de peças expedidas dia a dia.'],
        ['titulo' => 'FOs Liberadas por Dia','url' => ChartHelper::gerarGraficoBase64($chartFOs),'descricao' => 'Evolução das FOs liberadas diariamente.'],
        ['titulo' => 'Tempos Médios por Etapa','url' => ChartHelper::gerarGraficoBase64($chartTempos),'descricao' => 'Tempo médio das principais etapas da expedição.'],
        ['titulo' => 'Volume por Tipo de Carga','url' => ChartHelper::gerarGraficoBase64($chartTipos),'descricao' => 'Distribuição do volume expedido por tipo de carga.'],
        ['titulo' => 'Peso Total por Transportadora','url' => ChartHelper::gerarGraficoBase64($chartPesoTransportadoras),'descricao' => 'Transportadoras com maior peso movimentado.'],
    ];

    $pdf = Pdf::loadView('expedicao.relatorio_pdf', compact(
        'titulo_principal','data_hoje','data_referencia','graficos','topo'
    ));

    return $pdf->download("relatorio_expedicao_{$data_referencia}.pdf");
}








    
    
}
