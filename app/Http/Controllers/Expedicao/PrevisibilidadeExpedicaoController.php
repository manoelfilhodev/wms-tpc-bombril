<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\Expedicao\PrevisaoExpedicaoService;
use App\Services\Expedicao\ValidacaoOperacionalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrevisibilidadeExpedicaoController extends Controller
{
    public function index()
    {
        $validacaoService = new ValidacaoOperacionalService();

        $programacoes = ExpedicaoProgramacao::with('ultimaPrevisao')
            ->orderByRaw('agenda_entrega_em IS NULL')
            ->orderBy('agenda_entrega_em')
            ->get();

        $demandasPorFo = DB::table('_tb_demanda')
            ->whereIn('fo', $programacoes->pluck('fo')->filter()->unique()->values())
            ->get()
            ->keyBy('fo');

        $recalculosExecutados = 0;
        $maxRecalculosPorCarga = max(0, (int) config('services.expedicao_rotas.recalculate_per_request', 2));

        $programacoes->transform(function ($programacao) use ($validacaoService, $demandasPorFo, &$recalculosExecutados, $maxRecalculosPorCarga) {

            $demanda = $demandasPorFo->get($programacao->fo);

            $programacao->demanda = $demanda;

            if (
                $demanda &&
                $recalculosExecutados < $maxRecalculosPorCarga &&
                $this->previsaoPrecisaRecalculo($programacao)
            ) {
                try {
                    $recalculosExecutados++;
                    app(PrevisaoExpedicaoService::class)->calcular($programacao->id);
                    $programacao->load('ultimaPrevisao');
                } catch (\Throwable $e) {
                    Log::warning('Falha ao recalcular previsão no painel de expedição.', [
                        'fo' => $programacao->fo,
                        'erro' => $e->getMessage(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ETAPAS OPERACIONAIS
            |--------------------------------------------------------------------------
            */

            $etapas = [
                'separacao' => [
                    'label' => 'Separação',
                    'previsto' => optional($programacao->ultimaPrevisao)->tempo_separacao_min,
                    'inicio' => $demanda->separacao_iniciada_em ?? null,
                    'fim' => $demanda->separacao_finalizada_em ?? null,
                    'limite' => 480, // 8h
                ],

                'conferencia' => [
                    'label' => 'Conferência',
                    'previsto' => optional($programacao->ultimaPrevisao)->tempo_conferencia_min,
                    'inicio' => $demanda->conferencia_iniciada_em ?? null,
                    'fim' => $demanda->conferencia_finalizada_em ?? null,
                    'limite' => 240, // 4h
                ],

                'carregamento' => [
                    'label' => 'Carregamento',
                    'previsto' => optional($programacao->ultimaPrevisao)->tempo_carregamento_min,
                    'inicio' => $demanda->carregamento_iniciado_em ?? null,
                    'fim' => $demanda->carregamento_finalizado_em ?? null,
                    'limite' => 240, // 4h
                ],
            ];

            $desvioAcumuladoMin = 0;
            $possuiAnomaliaOperacional = false;

            foreach ($etapas as $chave => $etapa) {

                $realizadoMin = null;
                $desvioMin = null;
                $status = 'SEM_REALIZADO';
                $motivoAnomalia = null;

                /*
                |--------------------------------------------------------------------------
                | VALIDAÇÃO OPERACIONAL
                |--------------------------------------------------------------------------
                */

                $validacao = $validacaoService->validarEtapa(
                    $etapa['inicio'],
                    $etapa['fim'],
                    $etapa['limite']
                );

                /*
                |--------------------------------------------------------------------------
                | ANOMALIA
                |--------------------------------------------------------------------------
                */

                if ($validacao['anomalia']) {

                    $status = 'ANOMALIA_OPERACIONAL';
                    $motivoAnomalia = $validacao['motivo'];

                    $possuiAnomaliaOperacional = true;
                }

                /*
                |--------------------------------------------------------------------------
                | ETAPA VÁLIDA
                |--------------------------------------------------------------------------
                */

                elseif ($validacao['valido']) {

                    $realizadoMin = $validacao['realizado_min'];

                    $previstoMin = (int) ($etapa['previsto'] ?? 0);

                    $desvioMin = $realizadoMin - $previstoMin;

                    if ($desvioMin <= 0) {

                        $status = 'DENTRO_PREVISTO';

                    } else {

                        $status = 'FORA_PREVISTO';

                        $desvioAcumuladoMin += $desvioMin;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | RETORNO ETAPA
                |--------------------------------------------------------------------------
                */

                $etapas[$chave]['realizado'] = $realizadoMin;
                $etapas[$chave]['desvio'] = $desvioMin;
                $etapas[$chave]['status'] = $status;
                $etapas[$chave]['motivo_anomalia'] = $motivoAnomalia;
            }

            /*
            |--------------------------------------------------------------------------
            | RESULTADO GERAL
            |--------------------------------------------------------------------------
            */

            $programacao->etapas_operacionais = $etapas;
            $programacao->desvio_acumulado_min = $desvioAcumuladoMin;
            $programacao->possui_anomalia_operacional = $possuiAnomaliaOperacional;
            $programacao->agenda_vencida = $programacao->agenda_entrega_em
                ? $programacao->agenda_entrega_em->isPast()
                : false;
            $programacao->saida_concluida = ! empty($demanda->carregamento_finalizado_em);

            /*
            |--------------------------------------------------------------------------
            | SAÍDA PROJETADA
            |--------------------------------------------------------------------------
            */

            $programacao->saida_projetada_em = null;
            $programacao->desvio_saida_min = null;
            $programacao->status_saida_projetada = null;

            if (
                !$possuiAnomaliaOperacional &&
                $programacao->ultimaPrevisao &&
                $programacao->ultimaPrevisao->previsao_saida_caminhao
            ) {

                $saidaPrevistaOriginal = Carbon::parse(
                    $programacao->ultimaPrevisao->previsao_saida_caminhao
                );

                $saidaProjetada = $saidaPrevistaOriginal
                    ->copy()
                    ->addMinutes($desvioAcumuladoMin);

                $programacao->saida_projetada_em = $saidaProjetada;

                $programacao->desvio_saida_min =
                    $saidaPrevistaOriginal->diffInMinutes(
                        $saidaProjetada,
                        false
                    );

                $programacao->status_saida_projetada =
                    $programacao->desvio_saida_min > 0
                        ? 'FORA_PREVISTO'
                        : 'DENTRO_PREVISTO';
            }

            /*
            |--------------------------------------------------------------------------
            | STATUS GERAL OPERACIONAL
            |--------------------------------------------------------------------------
            */

            if ($possuiAnomaliaOperacional) {

                $programacao->status_operacional = 'ANOMALIA_OPERACIONAL';

            } elseif (! $demanda) {

                $programacao->status_operacional = 'SEM_EXPLOSAO';

            } elseif (
                $programacao->ultimaPrevisao?->status === 'ERRO' &&
                str_contains((string) $programacao->ultimaPrevisao->observacoes, 'Rota não encontrada')
            ) {

                $programacao->status_operacional = 'SEM_ROTA';

            } elseif (
                $programacao->ultimaPrevisao?->status === 'ERRO' &&
                str_contains((string) $programacao->ultimaPrevisao->observacoes, 'Critérios não encontrados')
            ) {

                $programacao->status_operacional = 'SEM_CRITERIO';

            } elseif ($programacao->agenda_vencida) {

                $programacao->status_operacional = 'ATRASADO';

            } elseif ($desvioAcumuladoMin > 30) {

                $programacao->status_operacional = 'ATRASADO';

            } elseif ($desvioAcumuladoMin > 0) {

                $programacao->status_operacional = 'ATENCAO';

            } else {

                $programacao->status_operacional = 'NO_PRAZO';
            }

            return $programacao;
        });

        $resumoOperacional = $this->montarResumoOperacional($programacoes);

        return view(
            'expedicao.previsibilidade.index',
            compact('programacoes', 'resumoOperacional')
        );
    }

    private function previsaoPrecisaRecalculo(ExpedicaoProgramacao $programacao): bool
    {
        $previsao = $programacao->ultimaPrevisao;

        if (! $previsao) {
            return true;
        }

        if (
            $previsao->status === 'ERRO' &&
            str_contains((string) $previsao->observacoes, 'Rota não encontrada')
        ) {
            return true;
        }

        if ($previsao->status === 'CALCULADO' && $previsao->tempo_viagem_min !== null) {
            $tempoRotaAtual = DB::table('_tb_expedicao_rotas')
                ->where('ativo', true)
                ->where('cidade_origem', config('services.expedicao_rotas.origin_city', 'Sao Bernardo do Campo'))
                ->where('uf_origem', config('services.expedicao_rotas.origin_uf', 'SP'))
                ->where('cidade_destino', $programacao->cidade_destino)
                ->where('uf_destino', $programacao->uf_destino)
                ->selectRaw('COALESCE(tempo_operacional_minutos, tempo_api_minutos) as tempo_viagem_min')
                ->value('tempo_viagem_min');

            return $tempoRotaAtual === null || (int) $tempoRotaAtual !== (int) $previsao->tempo_viagem_min;
        }

        return $previsao->status === 'ERRO'
            && $previsao->tempo_separacao_min === null
            && $previsao->tempo_conferencia_min === null
            && $previsao->tempo_carregamento_min === null;
    }

    private function montarResumoOperacional($programacoes): array
    {
        $total = $programacoes->count();

        $countEtapa = function (string $etapa) use ($programacoes): int {
            return $programacoes
                ->filter(fn ($programacao) => ($programacao->etapas_operacionais[$etapa]['realizado'] ?? null) !== null)
                ->count();
        };

        $countStatus = fn (array $status) => $programacoes
            ->filter(fn ($programacao) => in_array($programacao->status_operacional, $status, true))
            ->count();

        $cards = [
            [
                'titulo' => 'FO / DT',
                'valor' => $total,
                'percentual' => $this->percentualResumo($total, $total),
                'detalhe' => 'Programações exibidas',
                'icone' => 'mdi-format-list-numbered',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Destinos',
                'valor' => $programacoes
                    ->map(fn ($programacao) => trim(($programacao->cidade_destino ?? '-') . '/' . ($programacao->uf_destino ?? '-')))
                    ->unique()
                    ->count(),
                'percentual' => $this->percentualResumo(
                    $programacoes
                        ->map(fn ($programacao) => trim(($programacao->cidade_destino ?? '-') . '/' . ($programacao->uf_destino ?? '-')))
                        ->unique()
                        ->count(),
                    $total
                ),
                'detalhe' => 'Destinos únicos',
                'icone' => 'mdi-map-marker-radius-outline',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Agenda',
                'valor' => $programacoes->filter(fn ($programacao) => $programacao->agenda_entrega_em !== null)->count(),
                'percentual' => $this->percentualResumo(
                    $programacoes->filter(fn ($programacao) => $programacao->agenda_entrega_em !== null)->count(),
                    $total
                ),
                'detalhe' => 'Com agenda informada',
                'icone' => 'mdi-calendar-clock',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Tipo',
                'valor' => $programacoes->filter(fn ($programacao) => filled($programacao->tipo_carga))->count(),
                'percentual' => $this->percentualResumo(
                    $programacoes->filter(fn ($programacao) => filled($programacao->tipo_carga))->count(),
                    $total
                ),
                'detalhe' => $programacoes->pluck('tipo_carga')->filter()->unique()->count() . ' tipos de carga',
                'icone' => 'mdi-package-variant-closed',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Separação',
                'valor' => $countEtapa('separacao'),
                'percentual' => $this->percentualResumo($countEtapa('separacao'), $total),
                'detalhe' => 'Etapa executada',
                'icone' => 'mdi-package-variant-closed-check',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Conferência',
                'valor' => $countEtapa('conferencia'),
                'percentual' => $this->percentualResumo($countEtapa('conferencia'), $total),
                'detalhe' => 'Etapa executada',
                'icone' => 'mdi-clipboard-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Carregamento',
                'valor' => $countEtapa('carregamento'),
                'percentual' => $this->percentualResumo($countEtapa('carregamento'), $total),
                'detalhe' => 'Etapa executada',
                'icone' => 'mdi-truck-cargo-container',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Saída Prevista',
                'valor' => $programacoes->filter(fn ($programacao) => $programacao->ultimaPrevisao?->previsao_saida_caminhao !== null)->count(),
                'percentual' => $this->percentualResumo(
                    $programacoes->filter(fn ($programacao) => $programacao->ultimaPrevisao?->previsao_saida_caminhao !== null)->count(),
                    $total
                ),
                'detalhe' => 'Previsão calculada',
                'icone' => 'mdi-calendar-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Saída Projetada',
                'valor' => $programacoes->filter(fn ($programacao) => $programacao->saida_projetada_em !== null)->count(),
                'percentual' => $this->percentualResumo(
                    $programacoes->filter(fn ($programacao) => $programacao->saida_projetada_em !== null)->count(),
                    $total
                ),
                'detalhe' => 'Projeção disponível',
                'icone' => 'mdi-clock-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Status',
                'valor' => $countStatus(['NO_PRAZO']),
                'percentual' => $this->percentualResumo($countStatus(['NO_PRAZO']), $total),
                'detalhe' => 'No prazo',
                'icone' => 'mdi-check-decagram-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Atenção',
                'valor' => $countStatus(['ATENCAO', 'SEM_EXPLOSAO', 'SEM_ROTA', 'SEM_CRITERIO', 'ANOMALIA_OPERACIONAL']),
                'percentual' => $this->percentualResumo(
                    $countStatus(['ATENCAO', 'SEM_EXPLOSAO', 'SEM_ROTA', 'SEM_CRITERIO', 'ANOMALIA_OPERACIONAL']),
                    $total
                ),
                'detalhe' => 'Exige acompanhamento',
                'icone' => 'mdi-alert-circle-outline',
                'classe' => 'warning',
            ],
            [
                'titulo' => 'Atrasados',
                'valor' => $countStatus(['ATRASADO']),
                'percentual' => $this->percentualResumo($countStatus(['ATRASADO']), $total),
                'detalhe' => 'Fora do previsto',
                'icone' => 'mdi-truck-alert-outline',
                'classe' => 'danger',
            ],
        ];

        return [
            'total' => $total,
            'cards' => $cards,
        ];
    }

    private function percentualResumo(int $valor, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(($valor / $total) * 100, 1);
    }
}
