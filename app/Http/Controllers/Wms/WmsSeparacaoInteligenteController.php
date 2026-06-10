<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Wms\WmsSeparacaoFolha;
use App\Models\Wms\WmsSeparacaoGeracao;
use App\Services\SystemLogService;
use App\Services\Wms\GeradorSeparacaoInteligenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class WmsSeparacaoInteligenteController extends Controller
{
    public function index()
    {
        $demandas = Demanda::query()
            ->where('status', 'A_SEPARAR')
            ->whereHas('itens', fn ($q) => $q->where('sobra', '>', 0)->where('bloqueado', false))
            ->whereDoesntHave('wmsSeparacaoGeracoes')
            ->withCount([
                'itens as total_skus_picking' => fn ($q) => $q->where('sobra', '>', 0)->where('bloqueado', false),
            ])
            ->withSum([
                'itens as total_caixas_picking' => fn ($q) => $q->where('sobra', '>', 0)->where('bloqueado', false),
            ], 'sobra')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'fo', 'cliente', 'status', 'created_at']);

        $geracoes = WmsSeparacaoGeracao::query()
            ->withCount('folhas')
            ->latest()
            ->limit(12)
            ->get();

        return view('wms.separacao-inteligente.index', compact('demandas', 'geracoes'));
    }

    public function store(Request $request, GeradorSeparacaoInteligenteService $service)
    {
        $dados = $request->validate([
            'fo' => ['required', 'string', 'max:80'],
            'criterio_agrupamento' => ['required', Rule::in([
                GeradorSeparacaoInteligenteService::AGRUPAMENTO_UNICA,
                GeradorSeparacaoInteligenteService::AGRUPAMENTO_RUA,
                GeradorSeparacaoInteligenteService::AGRUPAMENTO_CURVA,
                GeradorSeparacaoInteligenteService::AGRUPAMENTO_SKU,
            ])],
            'criterio_ordenacao' => ['required', Rule::in([
                GeradorSeparacaoInteligenteService::ORDENACAO_SKU,
                GeradorSeparacaoInteligenteService::ORDENACAO_CURVA,
                GeradorSeparacaoInteligenteService::ORDENACAO_ENDERECO,
                GeradorSeparacaoInteligenteService::ORDENACAO_ROTA,
                GeradorSeparacaoInteligenteService::ORDENACAO_INTELIGENTE,
                GeradorSeparacaoInteligenteService::ORDENACAO_INTELIGENTE_RECOMENDADA,
            ])],
            'equalizacao' => ['nullable', 'boolean'],
            'criterio_equalizacao' => ['nullable', Rule::in([
                GeradorSeparacaoInteligenteService::EQUALIZACAO_SKUS,
                GeradorSeparacaoInteligenteService::EQUALIZACAO_QUANTIDADE,
                GeradorSeparacaoInteligenteService::EQUALIZACAO_RUAS,
                GeradorSeparacaoInteligenteService::EQUALIZACAO_INTELIGENTE,
            ])],
            'quantidade_separadores' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $fo = trim($dados['fo']);
            $demanda = Demanda::query()
                ->where('fo', $fo)
                ->where('status', 'A_SEPARAR')
                ->whereHas('itens', fn ($q) => $q->where('sobra', '>', 0)->where('bloqueado', false))
                ->whereDoesntHave('wmsSeparacaoGeracoes')
                ->first();

            if (! $demanda) {
                return back()
                    ->withInput()
                    ->with('error', 'DT não encontrada, já gerada ou fora do status A_SEPARAR.');
            }

            $dados['criterio_equalizacao'] = $request->boolean('equalizacao')
                ? ($dados['criterio_equalizacao'] ?? GeradorSeparacaoInteligenteService::EQUALIZACAO_INTELIGENTE)
                : GeradorSeparacaoInteligenteService::EQUALIZACAO_NAO;

            $geracao = $service->gerar($demanda, $dados, Auth::id());

            SystemLogService::record([
                'module' => 'wms',
                'action' => 'wms_separacao_inteligente_gerada',
                'description' => "Usuário gerou separação inteligente para FO {$demanda->fo}.",
                'entity_type' => 'wms_separacao_geracao',
                'entity_id' => $geracao->id,
                'new_values' => [
                    'fo' => $demanda->fo,
                    'configuracao' => $dados,
                    'total_itens' => $geracao->total_itens,
                    'total_folhas' => $geracao->folhas()->count(),
                ],
            ]);

            return redirect()
                ->route('wms.separacao-inteligente.show', $geracao)
                ->with('success', 'Separação inteligente gerada com sucesso.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(WmsSeparacaoGeracao $geracao)
    {
        $geracao->load(['folhas' => fn ($q) => $q->orderBy('numero_folha'), 'itens']);

        return view('wms.separacao-inteligente.show', compact('geracao'));
    }

    public function imprimir(WmsSeparacaoFolha $folha)
    {
        $folha->load([
            'geracao',
            'itens' => fn ($q) => $q->orderBy('ordem_separacao'),
        ]);

        return view('wms.separacao-inteligente.imprimir', compact('folha'));
    }
}
