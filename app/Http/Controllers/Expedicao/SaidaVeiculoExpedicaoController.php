<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Services\Expedicao\TimelineDtService;
use App\Services\SystemLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaidaVeiculoExpedicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = strtoupper((string) $request->input('status', 'PENDENTES'));
        $status = in_array($status, ['PENDENTES', 'FINALIZADAS', 'TODAS'], true) ? $status : 'PENDENTES';

        $programacoes = ExpedicaoProgramacao::query()
            ->with('ultimaPrevisao')
            ->whereExists(fn ($query) => $this->whereCarregamentoFinalizado($query))
            ->when($status === 'PENDENTES', fn ($query) => $query->whereExists(fn ($query) => $this->whereSaidaPendente($query)))
            ->when($status === 'FINALIZADAS', fn ($query) => $query->whereExists(fn ($query) => $this->whereSaidaFinalizada($query)))
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('fo', 'like', "%{$busca}%")
                        ->orWhere('dt_sap', 'like', "%{$busca}%")
                        ->orWhere('cidade_destino', 'like', "%{$busca}%")
                        ->orWhere('cliente', 'like', "%{$busca}%");
                });
            })
            ->orderByRaw('agenda_entrega_em IS NULL')
            ->orderBy('agenda_entrega_em')
            ->paginate(15)
            ->withQueryString();

        $demandas = Demanda::whereIn('fo', $programacoes->getCollection()->pluck('fo'))
            ->get()
            ->keyBy('fo');

        $programacoes->getCollection()->transform(function (ExpedicaoProgramacao $programacao) use ($demandas) {
            $programacao->demanda = $demandas->get($programacao->fo);

            return $programacao;
        });

        $resumo = [
            'pendentes' => ExpedicaoProgramacao::query()
                ->whereExists(fn ($query) => $this->whereCarregamentoFinalizado($query))
                ->whereExists(fn ($query) => $this->whereSaidaPendente($query))
                ->count(),
            'finalizadas' => ExpedicaoProgramacao::query()
                ->whereExists(fn ($query) => $this->whereCarregamentoFinalizado($query))
                ->whereExists(fn ($query) => $this->whereSaidaFinalizada($query))
                ->count(),
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
        $programacao = ExpedicaoProgramacao::with('ultimaPrevisao')->where('fo', $fo)->firstOrFail();
        $demanda = Demanda::with(['distribuicoes', 'separador'])->where('fo', $fo)->firstOrFail();

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
}
