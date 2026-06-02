<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\Expedicao\ExpedicaoRota;
use App\Services\Expedicao\PrevisaoExpedicaoService;
use App\Services\Expedicao\ValidacaoOperacionalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrevisibilidadeExpedicaoController extends Controller
{
    private const DATA_OPERACIONAL_MINIMA = '2000-01-01 00:00:00';

    public function index(Request $request)
    {
        $validacaoService = new ValidacaoOperacionalService();
        $tipoDemanda = strtoupper((string) $request->input('tipo_demanda', 'TODAS'));
        $tipoDemanda = in_array($tipoDemanda, ExpedicaoProgramacao::tiposDemanda(), true) ? $tipoDemanda : 'TODAS';

        $programacoes = ExpedicaoProgramacao::with('ultimaPrevisao')
            ->when($tipoDemanda !== 'TODAS', fn ($query) => $query->where('tipo_demanda', $tipoDemanda))
            ->orderByRaw('agenda_entrega_em IS NULL')
            ->orderBy('agenda_entrega_em')
            ->get();

        if ($tipoDemanda !== ExpedicaoProgramacao::TIPO_PROGRAMADA) {
            $oportunidadesSemProgramacao = Demanda::query()
                ->where('tipo', 'EXPEDICAO')
                ->whereNotNull('separacao_finalizada_em')
                ->where('separacao_finalizada_em', '>=', self::DATA_OPERACIONAL_MINIMA)
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_expedicao_programacoes as ep')
                        ->whereColumn('ep.fo', '_tb_demanda.fo');
                })
                ->get()
                ->map(fn (Demanda $demanda) => $this->programacaoVirtual($demanda));

            $programacoes = $programacoes->concat($oportunidadesSemProgramacao)->values();
        }

        $demandasPorFo = Demanda::query()
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
                $programacao->exists &&
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
                    'inicio_previsto' => optional($programacao->ultimaPrevisao)->previsao_inicio_separacao,
                    'prazo' => optional($programacao->ultimaPrevisao)->previsao_inicio_conferencia,
                    'inicio' => $this->dataOperacionalValida($demanda->separacao_iniciada_em ?? null),
                    'fim' => $this->dataOperacionalValida($demanda->separacao_finalizada_em ?? null),
                    'limite' => 480, // 8h
                ],

                'conferencia' => [
                    'label' => 'Conferência',
                    'previsto' => optional($programacao->ultimaPrevisao)->tempo_conferencia_min,
                    'inicio_previsto' => optional($programacao->ultimaPrevisao)->previsao_inicio_conferencia,
                    'prazo' => optional($programacao->ultimaPrevisao)->previsao_inicio_carregamento,
                    'inicio' => $this->dataOperacionalValida($demanda->conferencia_iniciada_em ?? null),
                    'fim' => $this->dataOperacionalValida($demanda->conferencia_finalizada_em ?? null),
                    'limite' => 240, // 4h
                ],

                'carregamento' => [
                    'label' => 'Carregamento',
                    'previsto' => optional($programacao->ultimaPrevisao)->tempo_carregamento_min,
                    'inicio_previsto' => optional($programacao->ultimaPrevisao)->previsao_inicio_carregamento,
                    'prazo' => optional($programacao->ultimaPrevisao)->previsao_saida_caminhao,
                    'inicio' => $this->dataOperacionalValida($demanda->carregamento_iniciado_em ?? null),
                    'fim' => $this->dataOperacionalValida($demanda->carregamento_finalizado_em ?? null),
                    'limite' => 240, // 4h
                ],
            ];

            $desvioAcumuladoMin = 0;
            $possuiAnomaliaOperacional = false;
            $projecaoFimEtapaAnterior = null;
            $agora = now();

            foreach ($etapas as $chave => $etapa) {

                $realizadoMin = null;
                $desvioMin = null;
                $status = 'SEM_REALIZADO';
                $motivoAnomalia = null;
                $inicioPrevisto = $etapa['inicio_previsto'] ? Carbon::parse($etapa['inicio_previsto']) : null;
                $prazo = $etapa['prazo'] ? Carbon::parse($etapa['prazo']) : null;
                $inicioReal = $etapa['inicio'] ? Carbon::parse($etapa['inicio']) : null;
                $fimReal = $etapa['fim'] ? Carbon::parse($etapa['fim']) : null;
                $fimProjetado = null;

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
                | PROJEÇÃO POR PRAZO
                |--------------------------------------------------------------------------
                */

                else {

                    if ($validacao['valido']) {
                        $realizadoMin = $validacao['realizado_min'];
                    }

                    $previstoMin = (int) ($etapa['previsto'] ?? 0);

                    $inicioProjetado = $inicioPrevisto?->copy();

                    if ($projecaoFimEtapaAnterior && (! $inicioProjetado || $projecaoFimEtapaAnterior->greaterThan($inicioProjetado))) {
                        $inicioProjetado = $projecaoFimEtapaAnterior->copy();
                    }

                    if ($inicioReal && (! $inicioProjetado || $inicioReal->greaterThan($inicioProjetado))) {
                        $inicioProjetado = $inicioReal->copy();
                    }

                    if (! $inicioProjetado && $previstoMin > 0) {
                        $inicioProjetado = $agora->copy();
                    }

                    if ($fimReal) {
                        $fimProjetado = $fimReal->copy();
                    } elseif ($inicioProjetado && $previstoMin > 0) {
                        if ($agora->greaterThan($inicioProjetado)) {
                            $inicioProjetado = $agora->copy();
                        }

                        $fimProjetado = $inicioProjetado->copy()->addMinutes($previstoMin);

                        if ($inicioReal && $agora->greaterThan($fimProjetado)) {
                            $fimProjetado = $agora->copy();
                        }
                    }

                    if ($prazo && $fimProjetado) {
                        $desvioMin = (int) ceil($prazo->diffInMinutes($fimProjetado, false));
                        $status = $desvioMin > 0 ? 'FORA_PREVISTO' : 'DENTRO_PREVISTO';
                    } elseif ($prazo && $agora->greaterThan($prazo)) {
                        $desvioMin = (int) ceil($prazo->diffInMinutes($agora, false));
                        $status = 'FORA_PREVISTO';
                    }

                    if ($status === 'FORA_PREVISTO' && $desvioMin > 0) {
                        $desvioAcumuladoMin = max($desvioAcumuladoMin, $desvioMin);
                    }

                    if ($fimProjetado) {
                        $projecaoFimEtapaAnterior = $fimProjetado->copy();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | RETORNO ETAPA
                |--------------------------------------------------------------------------
                */

                $etapas[$chave]['realizado'] = $realizadoMin;
                $etapas[$chave]['desvio'] = $desvioMin;
                $etapas[$chave]['prazo'] = $prazo;
                $etapas[$chave]['fim_projetado'] = $fimProjetado;
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
            $programacao->carregamento_concluido = (bool) $this->dataOperacionalValida($demanda->carregamento_finalizado_em ?? null);
            $programacao->saida_concluida = (bool) $this->dataOperacionalValida($demanda->saida_veiculo_em ?? null);

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

                $saidaProjetada = $projecaoFimEtapaAnterior
                    ? $projecaoFimEtapaAnterior->copy()
                    : $saidaPrevistaOriginal->copy();

                $programacao->saida_projetada_em = $saidaProjetada;

                $programacao->desvio_saida_min =
                    (int) ceil($saidaPrevistaOriginal->diffInMinutes(
                        $saidaProjetada,
                        false
                    ));

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

        $hoje = now()->toDateString();
        $programacoes = $programacoes
            ->reject(function ($programacao) use ($hoje) {
                $saidaVeiculo = $this->dataOperacionalValida($programacao->demanda?->saida_veiculo_em ?? null);

                return $saidaVeiculo && $saidaVeiculo->toDateString() !== $hoje;
            })
            ->values();

        $resumoFinalizadas = $this->montarResumoFinalizadas($programacoes);
        $resumoOperacional = $this->montarResumoOperacional($programacoes, $resumoFinalizadas);
        $programacoes = $programacoes
            ->reject(fn ($programacao) => (bool) $programacao->carregamento_concluido)
            ->values();

        return view(
            'expedicao.previsibilidade.index',
            compact('programacoes', 'resumoOperacional', 'tipoDemanda', 'resumoFinalizadas')
        );
    }

    private function dataOperacionalValida($data): ?Carbon
    {
        if (empty($data)) {
            return null;
        }

        $carbon = Carbon::parse($data);

        return $carbon->gte(self::DATA_OPERACIONAL_MINIMA) ? $carbon : null;
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
            $cidadeOrigem = $this->normalizarTexto(config('services.expedicao_rotas.origin_city', 'Sao Bernardo do Campo'));
            $cidadeDestino = $this->normalizarTexto($programacao->cidade_destino);

            $rota = ExpedicaoRota::query()
                ->where('ativo', true)
                ->where('uf_origem', config('services.expedicao_rotas.origin_uf', 'SP'))
                ->where('uf_destino', $programacao->uf_destino)
                ->get()
                ->first(function (ExpedicaoRota $rota) use ($cidadeOrigem, $cidadeDestino) {
                    return $this->normalizarTexto($rota->cidade_origem) === $cidadeOrigem
                        && $this->normalizarTexto($rota->cidade_destino) === $cidadeDestino;
                });

            $tempoRotaAtual = app(PrevisaoExpedicaoService::class)->tempoViagemOperacionalCaminhao($rota);

            return $tempoRotaAtual === null || (int) $tempoRotaAtual !== (int) $previsao->tempo_viagem_min;
        }

        return $previsao->status === 'ERRO'
            && $previsao->tempo_separacao_min === null
            && $previsao->tempo_conferencia_min === null
            && $previsao->tempo_carregamento_min === null;
    }

    private function normalizarTexto(?string $valor): string
    {
        $valor = trim((string) $valor);
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) ?: $valor;

        return strtoupper($valor);
    }

    private function montarResumoOperacional($programacoes, array $resumoFinalizadas): array
    {
        $total = $programacoes->count();
        $programadas = $programacoes->where('tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA);
        $oportunidades = $programacoes->where('tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE);
        $executadas = fn ($itens) => $itens->filter(fn ($programacao) => (bool) $programacao->saida_concluida)->count();
        $riscoStatus = ['ATRASADO', 'ATENCAO', 'SEM_EXPLOSAO', 'SEM_ROTA', 'SEM_CRITERIO', 'ANOMALIA_OPERACIONAL'];

        $programadasExecutadas = $executadas($programadas);
        $oportunidadesExecutadas = $executadas($oportunidades);
        $programadasRisco = $programadas
            ->filter(fn ($programacao) => in_array($programacao->status_operacional, $riscoStatus, true))
            ->count();
        $oportunidadesRisco = $oportunidades
            ->filter(fn ($programacao) => in_array($programacao->status_operacional, $riscoStatus, true))
            ->count();

        $countEtapa = function (string $etapa) use ($programacoes): int {
            return $programacoes
                ->filter(fn ($programacao) => ($programacao->etapas_operacionais[$etapa]['realizado'] ?? null) !== null)
                ->count();
        };

        $countStatus = fn (array $status) => $programacoes
            ->filter(fn ($programacao) => in_array($programacao->status_operacional, $status, true))
            ->count();

        $executadasTotal = $programadasExecutadas + $oportunidadesExecutadas;
        $riscoTotal = $programadasRisco + $oportunidadesRisco;
        $atrasadas = $countStatus(['ATRASADO']);
        $aguardandoSaida = (int) data_get($resumoFinalizadas, 'carregadas.total', 0);
        $saidasHoje = (int) data_get($resumoFinalizadas, 'com_saida.total', 0);

        $cards = [
            [
                'titulo' => 'Demanda do Dia',
                'valor' => $total,
                'percentual' => $this->percentualResumo($executadasTotal, $total),
                'detalhe' => $programadas->count() . ' programadas | ' . $oportunidades->count() . ' oportunidades',
                'icone' => 'mdi-calendar-check-outline',
                'classe' => 'neutral',
            ],
            [
                'titulo' => 'Executadas',
                'valor' => $executadasTotal,
                'percentual' => $this->percentualResumo($executadasTotal, $total),
                'detalhe' => 'Saída de veículo registrada hoje',
                'icone' => 'mdi-truck-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Pendentes',
                'valor' => max(0, $total - $executadasTotal),
                'percentual' => $this->percentualResumo(max(0, $total - $executadasTotal), $total),
                'detalhe' => 'Ainda exigem acompanhamento',
                'icone' => 'mdi-progress-clock',
                'classe' => max(0, $total - $executadasTotal) > 0 ? 'warning' : 'ok',
            ],
            [
                'titulo' => 'Em Risco',
                'valor' => $riscoTotal,
                'percentual' => $this->percentualResumo($riscoTotal, $total),
                'detalhe' => $programadasRisco . ' programadas | ' . $oportunidadesRisco . ' oportunidades',
                'icone' => 'mdi-alert-decagram-outline',
                'classe' => $riscoTotal > 0 ? 'warning' : 'neutral',
            ],
            [
                'titulo' => 'Atrasadas',
                'valor' => $atrasadas,
                'percentual' => $this->percentualResumo($atrasadas, $total),
                'detalhe' => 'Fora do previsto',
                'icone' => 'mdi-alert-circle-outline',
                'classe' => $atrasadas > 0 ? 'danger' : 'ok',
            ],
            [
                'titulo' => 'Saídas Hoje',
                'valor' => $saidasHoje,
                'percentual' => $this->percentualResumo($saidasHoje, $total),
                'detalhe' => 'Ciclo fechado no dia',
                'icone' => 'mdi-flag-checkered',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Separação',
                'valor' => $countEtapa('separacao'),
                'percentual' => $this->percentualResumo($countEtapa('separacao'), $total),
                'detalhe' => 'DTs já separadas',
                'icone' => 'mdi-package-variant-closed',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Conferência',
                'valor' => $countEtapa('conferencia'),
                'percentual' => $this->percentualResumo($countEtapa('conferencia'), $total),
                'detalhe' => 'DTs já conferidas',
                'icone' => 'mdi-clipboard-check-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Carregamento',
                'valor' => $countEtapa('carregamento'),
                'percentual' => $this->percentualResumo($countEtapa('carregamento'), $total),
                'detalhe' => 'DTs já carregadas',
                'icone' => 'mdi-truck-outline',
                'classe' => 'ok',
            ],
            [
                'titulo' => 'Aguardando Saída',
                'valor' => $aguardandoSaida,
                'percentual' => $this->percentualResumo($aguardandoSaida, $total),
                'detalhe' => 'Carregadas sem saída',
                'icone' => 'mdi-clock-outline',
                'classe' => $aguardandoSaida > 0 ? 'warning' : 'ok',
            ],
        ];

        return [
            'total' => $total,
            'cards' => $cards,
        ];
    }

    private function montarResumoFinalizadas($programacoes): array
    {
        $carregadas = $programacoes->filter(fn ($programacao) => (bool) $programacao->carregamento_concluido);
        $aguardandoSaida = $carregadas->reject(fn ($programacao) => (bool) $programacao->saida_concluida);
        $comSaida = $carregadas->filter(fn ($programacao) => (bool) $programacao->saida_concluida);

        $mapItem = fn ($programacao, string $campoData) => [
                'dt' => $programacao->dt_sap ?: $programacao->fo,
                'fo' => $programacao->fo,
                'destino' => trim(($programacao->cidade_destino ?? '-') . '/' . ($programacao->uf_destino ?? '-'), '/'),
                'tipo' => $programacao->tipo_demanda_label,
                'finalizada_em' => $this->dataOperacionalValida($programacao->demanda?->{$campoData} ?? null)?->format('H:i'),
            ];

        $itensCarregadas = $aguardandoSaida
            ->sortByDesc(fn ($programacao) => $programacao->demanda?->carregamento_finalizado_em)
            ->take(18)
            ->map(fn ($programacao) => $mapItem($programacao, 'carregamento_finalizado_em'))
            ->values();

        $itensComSaida = $comSaida
            ->sortByDesc(fn ($programacao) => $programacao->demanda?->saida_veiculo_em)
            ->take(18)
            ->map(fn ($programacao) => $mapItem($programacao, 'saida_veiculo_em'))
            ->values();

        return [
            'total' => $carregadas->count(),
            'programadas' => $carregadas->where('tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA)->count(),
            'oportunidades' => $carregadas->where('tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE)->count(),
            'ativas' => max(0, $programacoes->count() - $carregadas->count()),
            'carregadas' => [
                'total' => $aguardandoSaida->count(),
                'programadas' => $aguardandoSaida->where('tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA)->count(),
                'oportunidades' => $aguardandoSaida->where('tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE)->count(),
                'itens' => $itensCarregadas,
            ],
            'com_saida' => [
                'total' => $comSaida->count(),
                'programadas' => $comSaida->where('tipo_demanda', ExpedicaoProgramacao::TIPO_PROGRAMADA)->count(),
                'oportunidades' => $comSaida->where('tipo_demanda', ExpedicaoProgramacao::TIPO_OPORTUNIDADE)->count(),
                'itens' => $itensComSaida,
            ],
        ];
    }

    private function percentualResumo(int $valor, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(($valor / $total) * 100, 1);
    }

    private function programacaoVirtual(Demanda $demanda): ExpedicaoProgramacao
    {
        $programacao = new ExpedicaoProgramacao([
            'fo' => $demanda->fo,
            'dt_sap' => $demanda->fo,
            'cliente' => $demanda->cliente,
            'transportadora' => $demanda->transportadora,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_OPORTUNIDADE,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_IMPORTACAO_OPORTUNIDADE,
            'possui_picking' => true,
        ]);
        $programacao->demanda = $demanda;

        return $programacao;
    }
}
