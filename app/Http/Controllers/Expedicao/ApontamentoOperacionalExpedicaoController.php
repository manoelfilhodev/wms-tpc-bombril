<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\SystemLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApontamentoOperacionalExpedicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = $request->input('status', 'todos');
        $status = in_array($status, ['todos', 'conferencia_pendente', 'carregamento_pendente'], true)
            ? $status
            : 'todos';
        $tipoDemanda = strtoupper((string) $request->input('tipo_demanda', 'TODAS'));
        $tipoDemanda = in_array($tipoDemanda, ExpedicaoProgramacao::tiposDemanda(), true) ? $tipoDemanda : 'TODAS';

        $baseProgramacoes = ExpedicaoProgramacao::query()
            ->orderByDesc('agenda_entrega_em')
            ->when($tipoDemanda !== 'TODAS', fn ($query) => $query->where('tipo_demanda', $tipoDemanda))
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('fo', 'like', "%{$busca}%")
                        ->orWhere('cidade_destino', 'like', "%{$busca}%")
                        ->orWhere('cliente', 'like', "%{$busca}%");
                });
            })
            ->whereExists(fn ($query) => $this->whereDemandaSeparada($query));

        $resumoFila = $this->montarResumoFila(clone $baseProgramacoes);

        $programacoes = $baseProgramacoes
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->when($status === 'conferencia_pendente', function ($query) {
                $query->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                        ->where(function ($query) {
                            $query->whereNull('d.conferencia_finalizada_em')
                                ->orWhere('d.conferencia_finalizada_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                        });
                });
            })
            ->when($status === 'carregamento_pendente', function ($query) {
                $query->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                        ->whereNotNull('d.conferencia_finalizada_em')
                        ->where('d.conferencia_finalizada_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                        ->where(function ($query) {
                            $query->whereNull('d.carregamento_finalizado_em')
                                ->orWhere('d.carregamento_finalizado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                        });
                });
            })
            ->paginate(20)
            ->withQueryString();

        $demandas = Demanda::whereIn('fo', $programacoes->getCollection()->pluck('fo'))
            ->get()
            ->keyBy('fo');

        $programacoes->getCollection()->transform(function (ExpedicaoProgramacao $programacao) use ($demandas) {
            $programacao->demanda = $demandas->get($programacao->fo);

            return $programacao;
        });

        return view('expedicao.apontamentos-operacionais.index', [
            'programacoes' => $programacoes,
            'busca' => $busca,
            'status' => $status,
            'tipoDemanda' => $tipoDemanda,
            'resumoFila' => $resumoFila,
        ]);
    }

    public function store(Request $request, string $fo)
    {
        $dados = $request->validate([
            'etapa' => ['required', Rule::in(['conferencia', 'carregamento'])],
            'acao' => ['required', Rule::in(['iniciar_agora', 'finalizar_agora', 'salvar_manual'])],
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date'],
        ]);

        $demanda = Demanda::where('fo', $fo)->first();

        if (! $demanda) {
            return back()->with('error', "Não é possível apontar tempos para a FO {$fo}: explosão/demanda não encontrada.");
        }

        $campos = $this->camposEtapa($dados['etapa']);
        $atualizacao = [];
        $oldValues = $demanda->only([
            'conferencia_iniciada_em',
            'conferencia_finalizada_em',
            'carregamento_iniciado_em',
            'carregamento_finalizado_em',
        ]);

        if ($dados['acao'] === 'iniciar_agora') {
            if ($demanda->{$campos['inicio']}) {
                return back()->with('error', 'Esta etapa já possui horário de início. Use o lápis para editar manualmente.');
            }

            if (
                $dados['etapa'] === 'carregamento' &&
                ! $this->dataOperacionalValida($demanda->conferencia_finalizada_em)
            ) {
                return back()->with('error', 'Finalize a conferência antes de iniciar o carregamento.');
            }

            $atualizacao[$campos['inicio']] = now();
        }

        if ($dados['acao'] === 'finalizar_agora') {
            if ($demanda->{$campos['fim']}) {
                return back()->with('error', 'Esta etapa já possui horário de fim. Use o lápis para editar manualmente.');
            }

            if (
                $dados['etapa'] === 'carregamento' &&
                ! $this->dataOperacionalValida($demanda->conferencia_finalizada_em)
            ) {
                return back()->with('error', 'Finalize a conferência antes de finalizar o carregamento.');
            }

            $atualizacao[$campos['fim']] = now();
        }

        if ($dados['acao'] === 'salvar_manual') {
            if ($request->filled('inicio')) {
                $atualizacao[$campos['inicio']] = Carbon::parse($dados['inicio']);
            }

            if ($request->filled('fim')) {
                $atualizacao[$campos['fim']] = Carbon::parse($dados['fim']);
            }
        }

        if ($atualizacao === []) {
            return back()->with('error', 'Informe pelo menos um horário para salvar o apontamento.');
        }

        $inicio = $atualizacao[$campos['inicio']] ?? $demanda->{$campos['inicio']};
        $fim = $atualizacao[$campos['fim']] ?? $demanda->{$campos['fim']};

        if ($inicio && $fim && Carbon::parse($fim)->lt(Carbon::parse($inicio))) {
            return back()->with('error', 'O fim da etapa não pode ser menor que o início.');
        }

        $demanda->update($atualizacao);

        SystemLogService::record([
            'module' => 'expedicao',
            'action' => 'apontamento_operacional_salvo',
            'description' => "Apontamento operacional de {$dados['etapa']} salvo para a FO {$fo}.",
            'entity_type' => 'demanda',
            'entity_id' => $demanda->id,
            'old_values' => $oldValues,
            'new_values' => array_merge(['fo' => $fo, 'etapa' => $dados['etapa'], 'acao' => $dados['acao']], $atualizacao),
        ]);

        return back()->with('success', 'Apontamento operacional salvo com sucesso.');
    }

    private function camposEtapa(string $etapa): array
    {
        return match ($etapa) {
            'conferencia' => [
                'inicio' => 'conferencia_iniciada_em',
                'fim' => 'conferencia_finalizada_em',
            ],
            'carregamento' => [
                'inicio' => 'carregamento_iniciado_em',
                'fim' => 'carregamento_finalizado_em',
            ],
        };
    }

    private function dataOperacionalValida($data): bool
    {
        return $data && Carbon::parse($data)->gte(Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function whereDemandaSeparada($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->whereNotNull('d.separacao_finalizada_em')
            ->where('d.separacao_finalizada_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function whereDemandaNaoExpedida($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->where(function ($query) {
                $query->whereNull('d.carregamento_finalizado_em')
                    ->orWhere('d.carregamento_finalizado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
            });
    }

    private function whereDemandaExpedida($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->whereNotNull('d.carregamento_finalizado_em')
            ->where('d.carregamento_finalizado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function montarResumoFila($baseProgramacoes): array
    {
        $emFila = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->count();

        $aguardandoConferencia = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_demanda as d')
                    ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                    ->where(function ($query) {
                        $query->whereNull('d.conferencia_iniciada_em')
                            ->orWhere('d.conferencia_iniciada_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                    })
                    ->where(function ($query) {
                        $query->whereNull('d.conferencia_finalizada_em')
                            ->orWhere('d.conferencia_finalizada_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                    });
            })
            ->count();

        $conferindo = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_demanda as d')
                    ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                    ->whereNotNull('d.conferencia_iniciada_em')
                    ->where('d.conferencia_iniciada_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                    ->where(function ($query) {
                        $query->whereNull('d.conferencia_finalizada_em')
                            ->orWhere('d.conferencia_finalizada_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                    });
            })
            ->count();

        $aguardandoCarregamento = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_demanda as d')
                    ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                    ->whereNotNull('d.conferencia_finalizada_em')
                    ->where('d.conferencia_finalizada_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                    ->where(function ($query) {
                        $query->whereNull('d.carregamento_iniciado_em')
                            ->orWhere('d.carregamento_iniciado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                    });
            })
            ->count();

        $carregando = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaNaoExpedida($query))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_demanda as d')
                    ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                    ->whereNotNull('d.carregamento_iniciado_em')
                    ->where('d.carregamento_iniciado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
                    ->where(function ($query) {
                        $query->whereNull('d.carregamento_finalizado_em')
                            ->orWhere('d.carregamento_finalizado_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
                    });
            })
            ->count();

        $finalizadas = (clone $baseProgramacoes)
            ->whereExists(fn ($query) => $this->whereDemandaExpedida($query))
            ->count();

        return [
            'em_fila' => $emFila,
            'aguardando_conferencia' => $aguardandoConferencia,
            'conferindo' => $conferindo,
            'aguardando_carregamento' => $aguardandoCarregamento,
            'carregando' => $carregando,
            'finalizadas' => $finalizadas,
        ];
    }
}
