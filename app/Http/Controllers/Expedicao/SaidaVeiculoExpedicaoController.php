<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\Expedicao\TimelineDtService;
use App\Services\SystemLogService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class SaidaVeiculoExpedicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = strtoupper((string) $request->input('status', 'PENDENTES'));
        $status = in_array($status, ['PENDENTES', 'FINALIZADAS', 'TODAS'], true) ? $status : 'PENDENTES';

        $programacoesBase = ExpedicaoProgramacao::query()
            ->with('ultimaPrevisao')
            ->whereExists(fn ($query) => $this->whereCarregamentoFinalizado($query))
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('fo', 'like', "%{$busca}%")
                        ->orWhere('dt_sap', 'like', "%{$busca}%")
                        ->orWhere('cidade_destino', 'like', "%{$busca}%")
                        ->orWhere('cliente', 'like', "%{$busca}%");
                });
            })
            ->orderByRaw('agenda_entrega_em IS NULL')
            ->orderBy('agenda_entrega_em');

        $programacoesComCarregamento = $this->programacoesComOportunidadesSemProgramacao($programacoesBase, $busca)
            ->filter(fn (ExpedicaoProgramacao $programacao) => $this->dataOperacionalValida($programacao->demanda?->carregamento_finalizado_em))
            ->values();

        $programacoesCollection = $programacoesComCarregamento
            ->when($status === 'PENDENTES', fn (Collection $items) => $items->filter(
                fn (ExpedicaoProgramacao $programacao) => ! $this->dataOperacionalValida($programacao->demanda?->saida_veiculo_em)
            ))
            ->when($status === 'FINALIZADAS', fn (Collection $items) => $items->filter(
                fn (ExpedicaoProgramacao $programacao) => $this->dataOperacionalValida($programacao->demanda?->saida_veiculo_em)
            ))
            ->values();

        $programacoes = $this->paginarCollection($programacoesCollection, 15, $request);

        $demandas = Demanda::whereIn('fo', $programacoes->getCollection()->pluck('fo'))
            ->get()
            ->keyBy('fo');

        $programacoes->getCollection()->transform(function (ExpedicaoProgramacao $programacao) use ($demandas) {
            $programacao->demanda = $demandas->get($programacao->fo);

            return $programacao;
        });

        $resumo = [
            'pendentes' => $programacoesComCarregamento->filter(
                fn (ExpedicaoProgramacao $programacao) => ! $this->dataOperacionalValida($programacao->demanda?->saida_veiculo_em)
            )->count(),
            'finalizadas' => $programacoesComCarregamento->filter(
                fn (ExpedicaoProgramacao $programacao) => $this->dataOperacionalValida($programacao->demanda?->saida_veiculo_em)
            )->count(),
        ];

        return view('expedicao.saida-veiculos.index', [
            'programacoes' => $programacoes,
            'busca' => $busca,
            'status' => $status,
            'resumo' => $resumo,
            'podeEditar' => $this->podeEditarSaida(),
        ]);
    }

    public function show(string $fo, TimelineDtService $timelineService)
    {
        $demanda = Demanda::with(['distribuicoes', 'separador'])->where('fo', $fo)->firstOrFail();
        $programacao = ExpedicaoProgramacao::with('ultimaPrevisao')->where('fo', $fo)->first()
            ?: $this->programacaoVirtual($demanda);

        $programacao->demanda = $demanda;

        return view('expedicao.saida-veiculos.show', [
            'programacao' => $programacao,
            'demanda' => $demanda,
            'timeline' => $timelineService->montar($demanda, $programacao),
            'podeEditar' => $this->podeEditarSaida(),
        ]);
    }

    public function store(Request $request, string $fo)
    {
        $dados = $request->validate([
            'observacao' => ['nullable', 'string', 'max:255'],
        ]);

        $demanda = Demanda::where('fo', $fo)->firstOrFail();

        if (! $this->dataOperacionalValida($demanda->carregamento_finalizado_em)) {
            return back()->with('error', 'Finalize o carregamento antes de registrar a saída do veículo.');
        }

        if ($this->dataOperacionalValida($demanda->saida_veiculo_em)) {
            return back()->with('error', 'A saída do veículo já foi registrada. Use editar se precisar corrigir.');
        }

        $oldValues = $demanda->only(['saida_veiculo_em', 'saida_veiculo_usuario_id', 'saida_veiculo_observacao']);

        $demanda->update([
            'saida_veiculo_em' => now(),
            'saida_veiculo_usuario_id' => Auth::id(),
            'saida_veiculo_observacao' => $dados['observacao'] ?? null,
        ]);

        $this->registrarLog($demanda, 'saida_veiculo_registrada', 'Saída do veículo registrada para a FO ' . $fo . '.', $oldValues);

        return back()->with('success', 'Saída do veículo registrada com sucesso.');
    }

    public function update(Request $request, string $fo)
    {
        if (! $this->podeEditarSaida()) {
            abort(403, 'Somente admin ou gestor pode editar a saída do veículo.');
        }

        $dados = $request->validate([
            'saida_veiculo_em' => ['required', 'date'],
            'observacao' => ['nullable', 'string', 'max:255'],
        ]);

        $demanda = Demanda::where('fo', $fo)->firstOrFail();

        if (! $this->dataOperacionalValida($demanda->carregamento_finalizado_em)) {
            return back()->with('error', 'Finalize o carregamento antes de editar a saída do veículo.');
        }

        $saida = Carbon::parse($dados['saida_veiculo_em']);

        if ($saida->lt(Carbon::parse($demanda->carregamento_finalizado_em))) {
            return back()->with('error', 'A saída do veículo não pode ser menor que o fim do carregamento.');
        }

        $oldValues = $demanda->only(['saida_veiculo_em', 'saida_veiculo_usuario_id', 'saida_veiculo_observacao']);

        $demanda->update([
            'saida_veiculo_em' => $saida,
            'saida_veiculo_usuario_id' => Auth::id(),
            'saida_veiculo_observacao' => $dados['observacao'] ?? null,
        ]);

        $this->registrarLog($demanda, 'saida_veiculo_editada', 'Saída do veículo editada para a FO ' . $fo . '.', $oldValues);

        return back()->with('success', 'Saída do veículo atualizada com sucesso.');
    }

    private function whereCarregamentoFinalizado($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->whereNotNull('d.carregamento_finalizado_em')
            ->where('d.carregamento_finalizado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function whereSaidaPendente($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->where(function ($query) {
                $query->whereNull('d.saida_veiculo_em')
                    ->orWhere('d.saida_veiculo_em', '<', Demanda::DATA_OPERACIONAL_MINIMA);
            });
    }

    private function whereSaidaFinalizada($query): void
    {
        $query->selectRaw('1')
            ->from('_tb_demanda as d')
            ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
            ->whereNotNull('d.saida_veiculo_em')
            ->where('d.saida_veiculo_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function dataOperacionalValida($data): bool
    {
        return $data && Carbon::parse($data)->gte(Demanda::DATA_OPERACIONAL_MINIMA);
    }

    private function podeEditarSaida(): bool
    {
        $tipo = strtolower((string) (Auth::user()?->tipo ?? session('tipo', '')));
        $nivel = strtolower((string) (Auth::user()?->nivel ?? session('nivel', '')));

        return in_array($tipo, ['admin', 'gestor'], true)
            || str_contains($nivel, 'admin')
            || str_contains($nivel, 'gestor');
    }

    private function registrarLog(Demanda $demanda, string $action, string $description, array $oldValues): void
    {
        SystemLogService::record([
            'module' => 'expedicao',
            'action' => $action,
            'description' => $description,
            'entity_type' => 'demanda',
            'entity_id' => $demanda->id,
            'old_values' => $oldValues,
            'new_values' => $demanda->only(['fo', 'saida_veiculo_em', 'saida_veiculo_usuario_id', 'saida_veiculo_observacao']),
        ]);
    }

    private function programacoesComOportunidadesSemProgramacao($baseProgramacoes, string $busca): Collection
    {
        $programacoes = (clone $baseProgramacoes)->get();
        $demandas = Demanda::whereIn('fo', $programacoes->pluck('fo')->filter())->get()->keyBy('fo');

        $programacoes->transform(function (ExpedicaoProgramacao $programacao) use ($demandas) {
            $programacao->demanda = $demandas->get($programacao->fo);

            return $programacao;
        });

        $oportunidades = $this->queryOportunidadesSemProgramacao($busca)
            ->get()
            ->map(fn (Demanda $demanda) => $this->programacaoVirtual($demanda));

        return $programacoes
            ->concat($oportunidades)
            ->sortBy(fn (ExpedicaoProgramacao $programacao) =>
                optional($programacao->agenda_entrega_em)->timestamp
                ?? optional($programacao->demanda?->carregamento_finalizado_em)->timestamp
                ?? optional($programacao->demanda?->created_at)->timestamp
                ?? PHP_INT_MAX
            )
            ->values();
    }

    private function queryOportunidadesSemProgramacao(string $busca)
    {
        return Demanda::query()
            ->where('tipo', 'EXPEDICAO')
            ->whereNotNull('carregamento_finalizado_em')
            ->where('carregamento_finalizado_em', '>=', Demanda::DATA_OPERACIONAL_MINIMA)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('_tb_expedicao_programacoes as ep')
                    ->whereColumn('ep.fo', '_tb_demanda.fo');
            })
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('fo', 'like', "%{$busca}%")
                        ->orWhere('cliente', 'like', "%{$busca}%")
                        ->orWhere('transportadora', 'like', "%{$busca}%");
                });
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
