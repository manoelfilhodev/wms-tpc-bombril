<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApontamentoOperacionalExpedicaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = $request->input('status', 'todos');

        $programacoes = ExpedicaoProgramacao::query()
            ->orderByDesc('agenda_entrega_em')
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('fo', 'like', "%{$busca}%")
                        ->orWhere('cidade_destino', 'like', "%{$busca}%")
                        ->orWhere('cliente', 'like', "%{$busca}%");
                });
            })
            ->when($status === 'sem_explosao', function ($query) {
                $query->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo');
                });
            })
            ->when($status === 'conferencia_pendente', function ($query) {
                $query->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                        ->whereNull('d.conferencia_finalizada_em');
                });
            })
            ->when($status === 'carregamento_pendente', function ($query) {
                $query->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                        ->whereNull('d.carregamento_finalizado_em');
                });
            })
            ->when($status === 'finalizadas', function ($query) {
                $query->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('_tb_demanda as d')
                        ->whereColumn('d.fo', '_tb_expedicao_programacoes.fo')
                        ->whereNotNull('d.conferencia_finalizada_em')
                        ->whereNotNull('d.carregamento_finalizado_em');
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

        if ($dados['acao'] === 'iniciar_agora') {
            $atualizacao[$campos['inicio']] = now();
        }

        if ($dados['acao'] === 'finalizar_agora') {
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
}
