<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\SystemLogService;
use App\Traits\ExpedicaoBuscaTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ApontamentoOperacionalExpedicaoController extends Controller
{
    use ExpedicaoBuscaTrait;

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
                $this->aplicarBuscaExpedicao($query, $busca, ['fo', 'dt_sap'], ['cidade_destino', 'cliente']);
            })
            ->whereExists(fn ($query) => $this->whereDemandaSeparada($query));

        $filaCompleta = $this->programacoesComOportunidadesSemProgramacao($baseProgramacoes, $tipoDemanda, $busca);
        $resumoFila = $this->montarResumoFila($filaCompleta);

        $programacoesFiltradas = $filaCompleta
            ->filter(fn (ExpedicaoProgramacao $programacao) => $this->demandaNaoExpedida($programacao->demanda))
            ->when($status === 'conferencia_pendente', fn (Collection $items) => $items->filter(
                fn (ExpedicaoProgramacao $programacao) => ! $this->dataOperacionalValida($programacao->demanda?->conferencia_finalizada_em)
            ))
            ->when($status === 'carregamento_pendente', fn (Collection $items) => $items->filter(
                fn (ExpedicaoProgramacao $programacao) =>
                    $this->dataOperacionalValida($programacao->demanda?->conferencia_finalizada_em)
                    && ! $this->dataOperacionalValida($programacao->demanda?->carregamento_finalizado_em)
            ))
            ->values();

        $programacoes = $this->paginarCollection($programacoesFiltradas, 20, $request);

        $demandas = Demanda::whereIn('fo', $programacoes->getCollection()->pluck('fo')->filter())
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

    private function montarResumoFila(Collection $programacoes): array
    {
        $emFila = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) => $this->demandaNaoExpedida($programacao->demanda))->count();
        $aguardandoConferencia = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) =>
            $this->demandaNaoExpedida($programacao->demanda)
            && ! $this->dataOperacionalValida($programacao->demanda?->conferencia_iniciada_em)
            && ! $this->dataOperacionalValida($programacao->demanda?->conferencia_finalizada_em)
        )->count();
        $conferindo = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) =>
            $this->demandaNaoExpedida($programacao->demanda)
            && $this->dataOperacionalValida($programacao->demanda?->conferencia_iniciada_em)
            && ! $this->dataOperacionalValida($programacao->demanda?->conferencia_finalizada_em)
        )->count();
        $aguardandoCarregamento = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) =>
            $this->demandaNaoExpedida($programacao->demanda)
            && $this->dataOperacionalValida($programacao->demanda?->conferencia_finalizada_em)
            && ! $this->dataOperacionalValida($programacao->demanda?->carregamento_iniciado_em)
        )->count();
        $carregando = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) =>
            $this->demandaNaoExpedida($programacao->demanda)
            && $this->dataOperacionalValida($programacao->demanda?->carregamento_iniciado_em)
            && ! $this->dataOperacionalValida($programacao->demanda?->carregamento_finalizado_em)
        )->count();
        $finalizadas = $programacoes->filter(fn (ExpedicaoProgramacao $programacao) =>
            $this->dataOperacionalValida($programacao->demanda?->carregamento_finalizado_em)
        )->count();

        return [
            'em_fila' => $emFila,
            'aguardando_conferencia' => $aguardandoConferencia,
            'conferindo' => $conferindo,
            'aguardando_carregamento' => $aguardandoCarregamento,
            'carregando' => $carregando,
            'finalizadas' => $finalizadas,
        ];
    }

    private function programacoesComOportunidadesSemProgramacao($baseProgramacoes, string $tipoDemanda, string $busca): Collection
    {
        $programacoes = (clone $baseProgramacoes)->get();
        $demandas = Demanda::whereIn('fo', $programacoes->pluck('fo')->filter())->get()->keyBy('fo');

        $programacoes->transform(function (ExpedicaoProgramacao $programacao) use ($demandas) {
            $programacao->demanda = $demandas->get($programacao->fo);

            return $programacao;
        });

        if ($tipoDemanda === ExpedicaoProgramacao::TIPO_PROGRAMADA) {
            return $programacoes->values();
        }

        $oportunidades = $this->queryOportunidadesSemProgramacao($busca)
            ->get()
            ->map(fn (Demanda $demanda) => $this->programacaoVirtual($demanda));

        return $programacoes
            ->concat($oportunidades)
            ->sortByDesc(fn (ExpedicaoProgramacao $programacao) =>
                optional($programacao->agenda_entrega_em)->timestamp
                ?? optional($programacao->demanda?->separacao_finalizada_em)->timestamp
                ?? optional($programacao->demanda?->created_at)->timestamp
                ?? 0
            )
            ->values();
    }

    private function queryOportunidadesSemProgramacao(string $busca)
    {
        return Demanda::query()
            ->where('tipo', 'EXPEDICAO')
            ->whereNotNull('separacao_finalizada_em')
            ->where('separacao_finalizada_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_expedicao_programacoes as ep')
                    ->whereColumn('ep.fo', '_tb_demanda.fo');
            })
            ->when($busca !== '', function ($query) use ($busca) {
                $this->aplicarBuscaExpedicao($query, $busca, ['fo'], ['cliente', 'transportadora']);
            });
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

    private function demandaNaoExpedida(?Demanda $demanda): bool
    {
        return (bool) $demanda && ! $this->dataOperacionalValida($demanda->carregamento_finalizado_em);
    }

    private function paginarCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
