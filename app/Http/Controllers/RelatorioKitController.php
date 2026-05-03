<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RelatorioKitController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function gerarRelatorio()
    {
        // 🔹 Sempre começar com D-1
        $dataReferencia = Carbon::yesterday();

        // Verifica se houve produção em D-1
        $existeProducao = DB::table('_tb_apontamentos_kits')
            ->whereDate('updated_at', $dataReferencia)
            ->where('status', 'APONTADO')
            ->exists();

        // Se não houve produção em D-1, buscar a última data com produção
        if (!$existeProducao) {
            $ultimaData = DB::table('_tb_apontamentos_kits')
                ->where('status', 'APONTADO')
                ->orderByDesc('updated_at')
                ->value(DB::raw('DATE(updated_at)'));

            if ($ultimaData) {
                $dataReferencia = Carbon::parse($ultimaData);
            }
        }

        // === BUSCAR DADOS DO BANCO ===
        $metaRealizado    = $this->dashboardService->getMetaRealizado($dataReferencia);
        $tempoMedio       = $this->dashboardService->getTempoMedioPaletes($dataReferencia);
        $producaoSemana   = $this->dashboardService->getProducaoAcumuladaSemana($dataReferencia);
        $producaoDiaria   = $this->dashboardService->getProducaoDiaria($dataReferencia);
        $producaoHora     = $this->dashboardService->getProducaoPorHora($dataReferencia);
        $producaoMaterial = $this->dashboardService->getProducaoPorMaterial($dataReferencia);
        $top5Paletes      = $this->dashboardService->getTop5Paletes($dataReferencia);

        // === MONTAR OS GRÁFICOS ===
        $graficos = [];

        // Meta x Realizado
        $graficos[] = [
            'titulo' => 'Meta x Realizado — ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'bar',
                'data' => [
                    'labels' => ['Planejado', 'Realizado'],
                    'datasets' => [[
                        'label' => 'Qtd',
                        'data' => [
                            $metaRealizado['planejado'] ?? 0,
                            $metaRealizado['realizado'] ?? 0
                        ],
                        'backgroundColor' => ['#0047ba', '#80e1a6']
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'legend' => ['display' => false],
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'top',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'descricao' => 'Compara o volume planejado (kits GERADOS + APONTADOS) com o volume efetivamente realizado (kits APONTADOS) no dia de referência.',
            'detalhes' => $metaRealizado['detalhes']
        ];

        // Tempo médio entre paletes (linha diária)
        $graficos[] = [
            'titulo' => 'Tempo Médio entre Paletes (min) — até ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'line',
                'data' => [
                    'labels' => array_column($tempoMedio['dias'], 'data'),
                    'datasets' => [[
                        'label' => 'Minutos',
                        'data' => array_column($tempoMedio['dias'], 'media'),
                        'borderColor' => '#0047ba',
                        'fill' => false
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'top',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'media_mensal' => $tempoMedio['media_mensal'],
            'descricao' => 'Mostra a evolução diária do tempo médio entre apontamentos de paletes. Abaixo do gráfico está indicada a média consolidada mensal, permitindo avaliar a consistência da operação.'
        ];

        // Produção acumulada na semana
        $graficos[] = [
            'titulo' => 'Produção Acumulada na Semana — até ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'line',
                'data' => [
                    'labels' => array_column($producaoSemana, 'data'),
                    'datasets' => [[
                        'label' => 'Qtd Acumulada',
                        'data' => array_column($producaoSemana, 'total'),
                        'borderColor' => '#0047ba',
                        'fill' => false
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'top',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'descricao' => 'Apresenta o volume acumulado de produção desde o início da semana até a data de referência, permitindo verificar a evolução em relação à meta semanal.'
        ];

        // Produção diária
        $graficos[] = [
            'titulo' => 'Produção Diária — até ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($producaoDiaria, 'data'),
                    'datasets' => [[
                        'label' => 'Qtd Produzida',
                        'data' => array_column($producaoDiaria, 'total'),
                        'backgroundColor' => '#0047ba'
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'top',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'descricao' => 'Apresenta o total produzido em cada dia do mês até a data de referência, permitindo comparações e identificação de variações na produtividade.'
        ];

        // Produção por hora
        $graficos[] = [
            'titulo' => 'Produção por Hora — ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($producaoHora, 'hora'),
                    'datasets' => [[
                        'label' => 'Qtd Produzida',
                        'data' => array_column($producaoHora, 'total'),
                        'backgroundColor' => '#80e1a6'
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'top',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'descricao' => 'Mostra a distribuição da produção ao longo das horas úteis (06h–22h) do dia de referência, permitindo identificar horários de maior ou menor produtividade.'
        ];

        // Produção por material
        $graficos[] = [
            'titulo' => 'Produção por Código de Material — até ' . $dataReferencia->format('d/m/Y'),
            'url' => $this->makeQuickChartBase64([
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($producaoMaterial, 'codigo_material'),
                    'datasets' => [[
                        'label' => 'Qtd Produzida',
                        'data' => array_column($producaoMaterial, 'total'),
                        'backgroundColor' => '#72cc99'
                    ]]
                ],
                'options' => [
                    'indexAxis' => 'y',
                    'plugins' => [
                        'datalabels' => [
                            'anchor' => 'end',
                            'align' => 'right',
                            'color' => 'black'
                        ]
                    ]
                ]
            ]),
            'descricao' => 'Indica os SKUs (códigos de material) mais produzidos, possibilitando identificar quais itens têm maior representatividade na produção.'
        ];

        // // Top 5 paletes
        // $graficos[] = [
        //     'titulo' => 'Top 5 Paletes Produzidos — ' . $dataReferencia->format('d/m/Y'),
        //     'url' => $this->makeQuickChartBase64([
        //         'type' => 'bar',
        //         'data' => [
        //             'labels' => array_column($top5Paletes, 'palete_uid'),
        //             'datasets' => [[
        //                 'label' => 'Qtd',
        //                 'data' => array_column($top5Paletes, 'quantidade'),
        //                 'backgroundColor' => '#3a5daf'
        //             ]]
        //         ],
        //         'options' => [
        //             'plugins' => [
        //                 'datalabels' => [
        //                     'anchor' => 'end',
        //                     'align' => 'top',
        //                     'color' => 'black'
        //                 ]
        //             ]
        //         ]
        //     ]),
        //     'descricao' => 'Lista os cinco paletes com maior volume de produção no dia de referência, auxiliando no monitoramento da performance individual de paletes.'
        // ];

        // === ENVIAR PARA VIEW PDF ===
        $data = [
            'titulo_principal' => 'OPERAÇÃO TPC - DEXCO',
            'graficos' => $graficos,
            'data_hoje' => Carbon::now()->format('d/m/Y H:i'),
            'data_referencia' => $dataReferencia->format('d/m/Y'),
            'usuario' => 'WMS - Online',
            'topo' => public_path('images/topo.png'),
        ];

        $pdf = Pdf::loadView('relatorios.producao', $data)->setPaper('a4', 'portrait');

        return $pdf->download("relatorio_producao_{$dataReferencia->format('Ymd')}.pdf");
    }

    /**
     * Gera gráfico QuickChart e retorna como Base64
     */
    private function makeQuickChartBase64(array $config): string
    {
        $config['plugins'] = ['datalabels' => []];

        $url = "https://quickchart.io/chart?c=" . urlencode(json_encode($config));
        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            return '';
        }
        return "data:image/png;base64," . base64_encode($imageData);
    }
}
