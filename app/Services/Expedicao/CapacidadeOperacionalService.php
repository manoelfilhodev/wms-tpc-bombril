<?php

namespace App\Services\Expedicao;

use Carbon\Carbon;

class CapacidadeOperacionalService
{
    public function __construct(
        private readonly ConsumoCapacidadeService $consumoService,
        private readonly RiscoOperacionalService $riscoService,
        private readonly CapacidadeAntecipacaoService $antecipacaoService,
    ) {
    }

    public function analisar(?Carbon $dataOperacao = null): array
    {
        $dataOperacao = ($dataOperacao ?: now())->copy();
        $consumo = $this->consumoService->calcular($dataOperacao);
        $demanda = $consumo['demanda'];
        $consumoContexto = $consumo['consumo'];
        $produtividade = $consumo['produtividade'];
        $taxaEfetiva = max(0.1, (float) $produtividade['taxa_efetiva_hora']);
        $capacidadeRestanteDt = (int) floor($taxaEfetiva * (float) $consumo['horas_restantes']);
        $capacidadeTotalDt = (int) $consumoContexto['geral'] + $capacidadeRestanteDt;
        $pendenciaObrigatoria = (int) $demanda['programadas_pendentes'] + (int) $demanda['backlog'];
        $folgaOperacionalDt = $capacidadeRestanteDt - $pendenciaObrigatoria;
        $risco = $this->riscoService->calcular($consumo, $capacidadeRestanteDt);
        $antecipacao = $this->antecipacaoService->avaliar($folgaOperacionalDt, $capacidadeRestanteDt, $risco);
        $capacidadeConsumidaPct = $capacidadeTotalDt > 0
            ? round(((int) $consumoContexto['geral'] / $capacidadeTotalDt) * 100, 1)
            : 0;
        $capacidadeCarteiraPct = $capacidadeTotalDt > 0
            ? round(((int) $consumoContexto['carteira'] / $capacidadeTotalDt) * 100, 1)
            : 0;
        $capacidadeParalelaPct = $capacidadeTotalDt > 0
            ? round(((int) $consumoContexto['paralela'] / $capacidadeTotalDt) * 100, 1)
            : 0;

        return [
            'data_operacao' => $consumo['data_operacao'],
            'headcount_estimado' => $consumo['headcount_estimado'],
            'horas_restantes' => $consumo['horas_restantes'],
            'capacidade' => [
                'total_dt' => $capacidadeTotalDt,
                'geral_total_dt' => $capacidadeTotalDt,
                'geral_consumida_dt' => (int) $consumoContexto['geral'],
                'geral_consumida_percentual' => $capacidadeConsumidaPct,
                'carteira_consumida_dt' => (int) $consumoContexto['carteira'],
                'carteira_consumida_percentual' => $capacidadeCarteiraPct,
                'programadas_consumidas_dt' => (int) $consumoContexto['programadas'],
                'oportunidades_consumidas_dt' => (int) $consumoContexto['oportunidades'],
                'paralela_consumida_dt' => (int) $consumoContexto['paralela'],
                'paralela_consumida_percentual' => $capacidadeParalelaPct,
                'consumida_dt' => (int) $consumoContexto['geral'],
                'restante_dt' => $capacidadeRestanteDt,
                'consumida_percentual' => $capacidadeConsumidaPct,
                'restante_percentual' => max(0, round(100 - $capacidadeConsumidaPct, 1)),
                'folga_operacional_dt' => $folgaOperacionalDt,
                'max_pallets_estimado' => $this->estimarCapacidadeEtapa($consumo, 'separacao'),
                'max_separacao_estimado' => $this->estimarCapacidadeEtapa($consumo, 'separacao'),
                'max_conferencia_estimado' => $this->estimarCapacidadeEtapa($consumo, 'conferencia'),
                'max_carregamentos_estimado' => $this->estimarCapacidadeEtapa($consumo, 'carregamento'),
            ],
            'demanda' => $demanda,
            'consumo' => $consumoContexto,
            'produtividade' => $produtividade,
            'etapas' => $this->montarEtapas($consumo),
            'antecipacao' => $antecipacao,
            'risco' => $risco,
            'cards' => $this->cards(
                $demanda,
                $consumoContexto,
                $capacidadeTotalDt,
                $capacidadeRestanteDt,
                $capacidadeConsumidaPct,
                $capacidadeCarteiraPct,
                $capacidadeParalelaPct,
                $antecipacao,
                $risco
            ),
        ];
    }

    private function montarEtapas(array $consumo): array
    {
        $horasRestantes = (float) $consumo['horas_restantes'];

        return collect($consumo['etapas'])->map(function (array $etapa) use ($horasRestantes) {
            $capacidade = (int) $etapa['executadas'] + (int) floor(((float) $etapa['taxa_hora']) * $horasRestantes);

            return [
                'executadas' => (int) $etapa['executadas'],
                'capacidade_estimada' => $capacidade,
                'consumo_percentual' => $capacidade > 0 ? round(((int) $etapa['executadas'] / $capacidade) * 100, 1) : 0,
            ];
        })->all();
    }

    private function estimarCapacidadeEtapa(array $consumo, string $etapa): int
    {
        $dados = $consumo['etapas'][$etapa] ?? ['executadas' => 0, 'taxa_hora' => 0];

        return (int) $dados['executadas'] + (int) floor(((float) $dados['taxa_hora']) * (float) $consumo['horas_restantes']);
    }

    private function cards(
        array $demanda,
        array $consumoContexto,
        int $capacidadeTotalDt,
        int $capacidadeRestanteDt,
        float $capacidadeConsumidaPct,
        float $capacidadeCarteiraPct,
        float $capacidadeParalelaPct,
        array $antecipacao,
        array $risco
    ): array {
        return [
            [
                'titulo' => 'Capacidade Geral',
                'valor' => $capacidadeTotalDt,
                'percentual' => $capacidadeConsumidaPct,
                'detalhe' => "{$consumoContexto['geral']} consumidas | {$capacidadeRestanteDt} restantes",
                'icone' => 'mdi-speedometer',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Capacidade Carteira',
                'valor' => $consumoContexto['carteira'],
                'percentual' => $capacidadeCarteiraPct,
                'detalhe' => "{$consumoContexto['programadas']} programadas | {$consumoContexto['oportunidades']} oportunidades",
                'icone' => 'mdi-clipboard-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Capacidade Paralela',
                'valor' => $consumoContexto['paralela'],
                'percentual' => $capacidadeParalelaPct,
                'detalhe' => 'Fora da carteira oficial de expedição',
                'icone' => 'mdi-source-branch',
                'classe' => $consumoContexto['paralela'] > 0 ? 'warning' : 'neutral',
            ],
            [
                'titulo' => 'Capacidade para Antecipação',
                'valor' => $antecipacao['quantidade_estimada'],
                'percentual' => $capacidadeRestanteDt > 0 ? round(($antecipacao['quantidade_estimada'] / $capacidadeRestanteDt) * 100, 1) : 0,
                'detalhe' => $antecipacao['label'],
                'icone' => 'mdi-trending-up',
                'classe' => $antecipacao['classe'],
            ],
            [
                'titulo' => 'Risco Operacional',
                'valor' => $risco['label'],
                'percentual' => min(100, round($risco['indice_pressao'] * 100, 1)),
                'detalhe' => "{$risco['tendencia']} | {$risco['impacto_estimado']}",
                'icone' => 'mdi-alert-decagram-outline',
                'classe' => $risco['classe'],
            ],
            [
                'titulo' => 'Backlog Previsto',
                'valor' => $risco['backlog_projetado'],
                'percentual' => $demanda['programadas_pendentes'] > 0
                    ? round(($risco['backlog_projetado'] / max(1, $demanda['programadas_pendentes'])) * 100, 1)
                    : 0,
                'detalhe' => "{$risco['impacto_proximo_turno']} prox. turno | {$risco['impacto_proximo_dia']} prox. dia",
                'icone' => 'mdi-clipboard-clock-outline',
                'classe' => $risco['backlog_projetado'] > 0 ? 'warning' : 'ok',
            ],
        ];
    }
}
