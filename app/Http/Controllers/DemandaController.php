<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demanda;
use App\Models\DemandaDistribuicao;
use App\Models\DemandaItem;
use App\Models\User;
use App\Exports\DemandasExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DemandaHistory;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DemandaController extends Controller
{
    private const SKUS_BLOQUEADOS = [
        '1101',
        '1163',
        '1112',
        '1312',
        '22291',
        '22298',
        '22307',
        '22308',
        '21842',
        '40285',
        '22297',
    ];

    // Listagem
    public function index(Request $request)
    {
        $query = Demanda::query();

        if ($request->filled('fo')) {
            $query->where('fo', 'like', "%{$request->fo}%");
        }

        if ($request->filled('transportadora') && ! $request->boolean('somente_sobra')) {
            $query->where('transportadora', 'like', "%{$request->transportadora}%");
        }

        if ($request->filled('cliente')) {
            $query->where('cliente', 'like', "%{$request->cliente}%");
        }

        $statusFiltros = collect((array) $request->input('status', []))
            ->filter()
            ->unique()
            ->values();

        if ($statusFiltros->isNotEmpty()) {
            $query->where(function ($statusQuery) use ($statusFiltros) {
                foreach ($statusFiltros as $status) {
                    if ($status === 'SEPARADO_PARCIAL') {
                        $statusQuery->orWhere(function ($q) {
                            $q->whereNotNull('separacao_finalizada_em')
                                ->where('separacao_resultado', 'PARCIAL');
                        });
                    } elseif ($status === 'SEPARADO') {
                        $statusQuery->orWhere(function ($q) {
                            $q->whereNotNull('separacao_finalizada_em')
                                ->where('separacao_resultado', 'COMPLETA');
                        });
                    } elseif ($status === 'A_SEPARAR') {
                        $statusQuery->orWhere(function ($q) {
                            $q->whereNull('separacao_finalizada_em')
                                ->whereNull('separacao_iniciada_em')
                                ->whereDoesntHave('distribuicoes');
                        });
                    } elseif ($status === 'SEPARANDO') {
                        $statusQuery->orWhere(function ($q) {
                            $q->whereNull('separacao_finalizada_em')
                                ->where(function ($emSeparacao) {
                                    $emSeparacao->whereNotNull('separacao_iniciada_em')
                                        ->orWhereHas('distribuicoes');
                                });
                        });
                    } else {
                        $statusQuery->orWhere('status', $status);
                    }
                }
            });
        }

        if ($request->boolean('somente_sobra')) {
            $query->where('possui_sobra', true);
            if (!$request->filled('data_inicio') && !$request->filled('data_fim')) {
                $hoje = Carbon::today()->format('Y-m-d');
                $request->merge([
                    'data_inicio' => $hoje,
                    'data_fim' => $hoje,
                ]);
            }
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            if ($request->data_inicio === $request->data_fim) {
                $query->whereDate('created_at', $request->data_inicio);
            } else {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay(),
                ]);
            }
        }

        $ordenacao = $request->input('ordem', 'mais_novas');

        $query = $query
            ->with(['separador', 'separadores', 'distribuicoes'])
            ->withCount(['itens as total_skus_picking' => function ($q) {
                $q->where('sobra', '>', 0)
                    ->select(DB::raw('COUNT(DISTINCT COALESCE(sku_normalizado, sku))'));
            }])
            ->withSum(['itens as total_pecas_picking' => function ($q) {
                $q->where('sobra', '>', 0);
            }], 'sobra')
            ->withSum('distribuicoes as total_pecas_distribuidas', 'quantidade_pecas');

        match ($ordenacao) {
            'mais_antigas' => $query->orderBy('created_at'),
            'dt_asc' => $query->orderBy('fo'),
            'dt_desc' => $query->orderByDesc('fo'),
            'itens_asc' => $query->orderBy('total_itens_com_sobra'),
            'itens_desc' => $query->orderByDesc('total_itens_com_sobra'),
            'picking_asc' => $query->orderBy('total_pecas_picking'),
            'picking_desc' => $query->orderByDesc('total_pecas_picking'),
            'saldo_asc' => $query->orderByRaw('(COALESCE(total_pecas_picking, 0) - COALESCE(total_pecas_distribuidas, 0)) asc'),
            'saldo_desc' => $query->orderByRaw('(COALESCE(total_pecas_picking, 0) - COALESCE(total_pecas_distribuidas, 0)) desc'),
            default => $query->orderByDesc('created_at'),
        };

        $demandas = $query->paginate(20)->withQueryString();
        $separadores = User::query()
            ->where('status', 1)
            ->orderBy('nome')
            ->get(['id_user', 'nome']);

        $modoOperacional = $request->boolean('somente_sobra');
        $resumoOperacional = $modoOperacional
            ? $this->getResumoOperacionalPorPeriodo($request->data_inicio, $request->data_fim)
            : null;

        return view('demandas.index', compact('demandas', 'modoOperacional', 'separadores', 'resumoOperacional'));
    }


    // Formulário de criação
    public function create()
    {
        return view('demandas.create');
    }

    // Salvar nova demanda
    public function store(Request $request)
    {
        $request->validate([
            'fo' => 'required|string|max:50',
            'cliente' => 'required|string|max:150',
            'tipo' => 'required|in:RECEBIMENTO,EXPEDICAO',
        ]);

        Demanda::create([
            'fo' => $request->fo,
            'cliente' => $request->cliente,
            'transportadora' => $request->transportadora,
            'doca' => $request->doca,
            'tipo' => $request->tipo,
            'quantidade' => $request->quantidade,
            'peso' => $request->peso,
            'valor_carga' => $request->valor_carga,
            'hora_agendada' => $request->hora_agendada,
            'entrada' => $request->entrada,
            'saida' => $request->saida,
            'status' => 'GERAR', // sempre inicia em GERAR
        ]);

        return redirect()->route('demandas.index')->with('success', 'Demanda lançada com sucesso!');
    }

    // Exibir formulário de edição
    public function edit($id)
    {
        $demanda = Demanda::findOrFail($id);
        return view('demandas.edit', compact('demanda'));
    }

    // Atualizar demanda
    public function update(Request $request, $id)
    {
        $request->validate([
            'fo'              => 'required|string|max:50',
            'cliente'         => 'required|string|max:150',
            'tipo'            => 'required|in:RECEBIMENTO,EXPEDICAO',
            'transportadora'  => 'nullable|string|max:150',
            'doca'            => 'nullable|string|max:10',
            'quantidade'      => 'nullable|integer|min:0',
            'peso'            => 'nullable|numeric',
            'valor_carga'     => 'nullable|numeric',
            'hora_agendada'   => 'nullable',
            'entrada'         => 'nullable',
            'saida'           => 'nullable',
            'veiculo'         => 'nullable|string|max:50',
            'modelo_veicular' => 'nullable|string|max:150',
            'motorista'       => 'nullable|string|max:150',
        ]);

        $demanda = Demanda::findOrFail($id);

        $demanda->update([
            'fo'              => $request->fo,
            'cliente'         => $request->cliente,
            'transportadora'  => $request->transportadora,
            'doca'            => $request->doca,
            'tipo'            => $request->tipo,
            'quantidade'      => $request->quantidade ?? 0,
            'peso'            => $request->peso ?? 0,
            'valor_carga'     => $request->valor_carga ?? 0,
            'hora_agendada'   => $request->hora_agendada,
            'entrada'         => $request->entrada,
            'saida'           => $request->saida,
            'veiculo'         => $request->veiculo,
            'modelo_veicular' => $request->modelo_veicular,
            'motorista'       => $request->motorista,
            // status não editamos aqui para não quebrar o fluxo
        ]);

        return redirect()->route('demandas.index')->with('success', 'Demanda atualizada com sucesso!');
    }


    public function destroy($id)
    {
        $demanda = Demanda::findOrFail($id);
        $demanda->delete();

        return redirect()->route('demandas.index')->with('success', 'Demanda excluída com sucesso!');
    }

    public function export(Request $request)
    {
        return Excel::download(new DemandasExport($request), 'demandas_filtradas.xlsx');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);

        $demanda = Demanda::findOrFail($id);
        $demanda->status = $request->status;
        $demanda->save();

        // Salvar histórico
        DemandaHistory::create([
            'demanda_id' => $demanda->id,
            'status' => $request->status,
            'changed_by' => auth()->user()->id_user ?? null,
        ]);

        return back()->with('success', "Status da FO {$demanda->fo} atualizado!");
    }

    public function updateMultiple(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'ids' => 'required|array'
        ]);

        foreach ($request->ids as $id) {
            $demanda = Demanda::find($id);
            if ($demanda) {
                $demanda->update(['status' => $request->status]);

                DemandaHistory::create([
                    'demanda_id' => $demanda->id,
                    'status' => $request->status,
                    'changed_by' => auth()->user()->id_user ?? null,
                ]);
            }
        }

        return back()->with('success', 'Status atualizado em lote com sucesso!');
    }

    public function import(Request $request)
    {
        if (!$request->filled('planilha')) {
            return back()->with('error', 'Nenhum dado foi enviado.');
        }

        $linhas = preg_split("/\r\n|\n|\r/", trim($request->planilha));
        if (count($linhas) < 2) {
            return back()->with('error', 'Planilha inválida.');
        }

        $cabecalho = preg_split("/\t+/", trim($linhas[0]));
        $mapa = $this->mapearCabecalho($cabecalho);

        if (!$mapa['is_sap']) {
            return back()->with('error', 'Formato não reconhecido. Use a exportação SAP com colunas Transporte, Material e Sobra.');
        }

        $resumoPorDt = [];
        $itensImportados = 0;
        $itensIgnoradosBloqueio = 0;

        DB::transaction(function () use ($linhas, $mapa, &$resumoPorDt, &$itensImportados, &$itensIgnoradosBloqueio) {
            foreach ($linhas as $index => $linha) {
                if ($index === 0 || trim($linha) === '') {
                    continue;
                }

                $colunas = preg_split("/\t+/", trim($linha));
                $dt = trim($colunas[$mapa['transporte']] ?? '');
                $skuOriginal = trim($colunas[$mapa['material']] ?? '');

                if ($dt === '' || $skuOriginal === '') {
                    continue;
                }

                $skuNormalizado = $this->normalizarSku($skuOriginal);
                $isBloqueado = in_array($skuNormalizado, self::SKUS_BLOQUEADOS, true);
                if ($isBloqueado) {
                    $itensIgnoradosBloqueio++;
                    continue;
                }

                $sobra = $this->converteNumero($colunas[$mapa['sobra']] ?? '0');
                $temSobra = $sobra > 0;

                if (!isset($resumoPorDt[$dt])) {
                    $demanda = Demanda::updateOrCreate(
                        ['fo' => $dt],
                        [
                            'cliente' => $colunas[$mapa['nome']] ?? null,
                            'transportadora' => $colunas[$mapa['transportadora']] ?? null,
                            'tipo' => 'EXPEDICAO',
                            'status' => 'A_SEPARAR',
                            'hora_agendada' => null,
                            'total_itens' => 0,
                            'total_itens_com_sobra' => 0,
                            'possui_sobra' => false,
                        ]
                    );

                    DemandaItem::where('demanda_id', $demanda->id)->delete();
                    $resumoPorDt[$dt] = ['demanda_id' => $demanda->id, 'total' => 0, 'com_sobra' => 0];
                }

                DemandaItem::create([
                    'demanda_id' => $resumoPorDt[$dt]['demanda_id'],
                    'sku' => $skuOriginal,
                    'sku_normalizado' => $skuNormalizado,
                    'descricao' => $colunas[$mapa['descricao']] ?? null,
                    'unidade_medida' => $colunas[$mapa['unidade']] ?? null,
                    'sobra' => $sobra,
                    'bloqueado' => false,
                ]);

                $resumoPorDt[$dt]['total']++;
                $resumoPorDt[$dt]['com_sobra'] += $temSobra ? 1 : 0;
                $itensImportados++;
            }

            foreach ($resumoPorDt as $dt => $resumo) {
                Demanda::where('id', $resumo['demanda_id'])->update([
                    'total_itens' => $resumo['total'],
                    'total_itens_com_sobra' => $resumo['com_sobra'],
                    'possui_sobra' => $resumo['com_sobra'] > 0,
                    'status' => $resumo['com_sobra'] > 0 ? 'A_SEPARAR' : 'GERAR',
                ]);
            }
        });

        $dtsComSobra = collect($resumoPorDt)->filter(fn($r) => $r['com_sobra'] > 0)->count();
        return back()->with(
            'success',
            "Importação concluída. Itens válidos: {$itensImportados}. Itens bloqueados: {$itensIgnoradosBloqueio}. DTs com sobra: {$dtsComSobra}."
        );
    }

    private function converteNumero($valor)
    {
        if (!$valor || trim($valor) === '') {
            return 0;
        }

        // converte 10.291,34 → 10291.34
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return (float) $valor;
    }

    private function normalizarSku(?string $sku): string
    {
        $sku = preg_replace('/\D+/', '', (string) $sku);
        $sku = ltrim($sku, '0');
        return $sku === '' ? '0' : $sku;
    }

    private function mapearCabecalho(array $cabecalho): array
    {
        $normalizados = [];
        foreach ($cabecalho as $idx => $coluna) {
            $k = mb_strtolower(trim($coluna));
            $normalizados[$k] = $idx;
        }

        return [
            'is_sap' => isset($normalizados['transporte'], $normalizados['material'], $normalizados['sobra']),
            'transporte' => $normalizados['transporte'] ?? null,
            'transportadora' => $normalizados['transportadora'] ?? null,
            'material' => $normalizados['material'] ?? null,
            'sobra' => $normalizados['sobra'] ?? null,
            'unidade' => $normalizados['unid.medida básica'] ?? null,
            'descricao' => $normalizados['texto breve material'] ?? null,
            'nome' => $normalizados['nome'] ?? null,
        ];
    }

    public function operacional(Request $request)
    {
        $request->merge(['somente_sobra' => 1]);
        return $this->index($request);
    }

    public function updateStage(Request $request, $id)
    {
        $request->validate([
            'stage' => 'nullable|string|max:100',
        ]);

        $demanda = Demanda::findOrFail($id);
        $stage = trim((string) $request->input('stage', ''));

        if (!Schema::hasColumn('_tb_demanda', 'stage')) {
            return back()->with('error', 'A coluna Stage ainda não existe no banco. Execute as migrations antes de atualizar.');
        }

        $demanda->update([
            'stage' => $stage !== '' ? $stage : null,
        ]);

        return back()->with('success', "Stage da DT {$demanda->fo} atualizado.");
    }

    public function distribuirDt(Request $request, $id)
    {
        $request->validate([
            'separador_nome' => 'required|string|max:150',
            'quantidade_pecas' => 'required|integer|min:1',
            'quantidade_skus' => 'required|integer|min:1',
        ]);

        $demanda = Demanda::withCount(['itens as total_skus_picking' => function ($q) {
            $q->where('sobra', '>', 0)
                ->select(DB::raw('COUNT(DISTINCT COALESCE(sku_normalizado, sku))'));
        }])
            ->withSum(['itens as total_pecas_picking' => function ($q) {
                $q->where('sobra', '>', 0);
            }], 'sobra')
            ->withSum('distribuicoes as total_pecas_distribuidas', 'quantidade_pecas')
            ->withSum('distribuicoes as total_skus_distribuidos', 'quantidade_skus')
            ->findOrFail($id);

        $totalPicking = (int) round((float) ($demanda->total_pecas_picking ?? 0));
        $totalSkusPicking = (int) ($demanda->total_skus_picking ?? 0);
        $distribuido = (int) ($demanda->total_pecas_distribuidas ?? 0);
        $skusDistribuidos = (int) ($demanda->total_skus_distribuidos ?? 0);
        $restante = max(0, $totalPicking - $distribuido);
        $skusRestantes = max(0, $totalSkusPicking - $skusDistribuidos);
        $qtd = (int) $request->quantidade_pecas;
        $qtdSkus = (int) $request->quantidade_skus;

        if ($totalPicking <= 0) {
            return back()->with('error', "A DT {$demanda->fo} não possui peças de picking para distribuir.");
        }
        if ($qtd > $restante) {
            return back()->with('error', "Quantidade inválida. Restante disponível para distribuição: {$restante} peças.");
        }
        if ($qtdSkus > $skusRestantes) {
            return back()->with('error', "Quantidade de SKUs inválida. Restante disponível para distribuição: {$skusRestantes} SKU(s).");
        }

        $separadorNome = trim($request->separador_nome);
        $distribuicaoAberta = DemandaDistribuicao::query()
            ->where('demanda_id', $demanda->id)
            ->where('separador_nome', $separadorNome)
            ->whereNull('finalizado_em')
            ->first();

        if ($distribuicaoAberta) {
            $distribuicaoAberta->increment('quantidade_pecas', $qtd);
            $distribuicaoAberta->increment('quantidade_skus', $qtdSkus);
        } else {
            DemandaDistribuicao::create([
                'demanda_id' => $demanda->id,
                'separador_nome' => $separadorNome,
                'quantidade_pecas' => $qtd,
                'quantidade_skus' => $qtdSkus,
            ]);
        }

        if (!$demanda->separacao_iniciada_em) {
            $demanda->update([
                'separacao_iniciada_em' => now(),
                'status' => 'SEPARANDO',
            ]);
        }

        return back()->with('success', "Distribuição registrada na DT {$demanda->fo}.");
    }

    public function finalizarSeparador(Request $request, $id)
    {
        $request->validate([
            'separador_nome' => 'required|string|max:150',
            'resultado' => 'required|in:PARCIAL,COMPLETA',
        ]);

        $demanda = Demanda::findOrFail($id);
        $separadorNome = trim($request->separador_nome);

        $distribuicao = DemandaDistribuicao::query()
            ->where('demanda_id', $demanda->id)
            ->where('separador_nome', $separadorNome)
            ->whereNull('finalizado_em')
            ->first();

        if (! $distribuicao) {
            return back()->with('error', "Não existe distribuição em aberto para o separador {$separadorNome} na DT {$demanda->fo}.");
        }

        $distribuicao->update([
            'finalizado_em' => now(),
            'resultado' => $request->resultado,
        ]);

        $totalPicking = (int) round((float) $demanda->itens()->where('sobra', '>', 0)->sum('sobra'));
        $totalDistribuido = (int) $demanda->distribuicoes()->sum('quantidade_pecas');
        $abertas = (int) $demanda->distribuicoes()->whereNull('finalizado_em')->count();

        if ($totalPicking > 0 && $totalDistribuido >= $totalPicking && $abertas === 0) {
            $temParcial = $demanda->distribuicoes()->where('resultado', 'PARCIAL')->exists();
            $demanda->update([
                'separacao_finalizada_em' => now(),
                'separacao_resultado' => $temParcial ? 'PARCIAL' : 'COMPLETA',
                'status' => $temParcial ? 'A_CONFERIR' : 'CONFERIDO',
            ]);
        }

        return back()->with('success', "Separador {$separadorNome} finalizado na DT {$demanda->fo} ({$request->resultado}).");
    }

    public function iniciarSeparacao($id)
    {
        request()->validate([
            'separador_ids' => 'nullable|array',
            'separador_ids.*' => 'integer|exists:_tb_usuarios,id_user',
        ]);

        $demanda = Demanda::findOrFail($id);
        if (! $demanda->possui_sobra) {
            return back()->with('error', "A DT {$demanda->fo} não possui sobra para separação.");
        }
        if ($demanda->separacao_iniciada_em && ! $demanda->separacao_finalizada_em) {
            return back()->with('error', "A DT {$demanda->fo} já está em separação.");
        }

        $separadorIds = array_values(array_unique(array_map('intval', (array) request('separador_ids', []))));
        $demanda->update([
            'separador_id' => $separadorIds[0] ?? $demanda->separador_id,
            'separacao_iniciada_em' => now(),
            'separacao_finalizada_em' => null,
            'separacao_resultado' => null,
            'status' => 'SEPARANDO',
        ]);
        if (!empty($separadorIds)) {
            $demanda->separadores()->sync($separadorIds);
        }

        return back()->with('success', "Separação da DT {$demanda->fo} iniciada.");
    }

    public function finalizarSeparacao(Request $request, $id)
    {
        $request->validate([
            'resultado' => 'required|in:PARCIAL,COMPLETA',
        ]);

        $demanda = Demanda::findOrFail($id);
        if (! $demanda->separacao_iniciada_em) {
            $totalDistribuido = (int) $demanda->distribuicoes()->sum('quantidade_pecas');
            if ($totalDistribuido > 0) {
                $primeiraDistribuicao = $demanda->distribuicoes()->orderBy('created_at')->first();
                $demanda->separacao_iniciada_em = $primeiraDistribuicao?->created_at ?? now();
            } else {
                return back()->with('error', "A DT {$demanda->fo} ainda não foi iniciada.");
            }
        }
        if ($demanda->separacao_finalizada_em) {
            return back()->with('error', "A DT {$demanda->fo} já foi finalizada.");
        }
        $demanda->separacao_finalizada_em = now();
        $demanda->separacao_resultado = $request->resultado;
        $demanda->status = $request->resultado === 'COMPLETA' ? 'CONFERIDO' : 'A_CONFERIR';
        $demanda->save();

        return back()->with('success', "Separação da DT {$demanda->fo} finalizada como {$request->resultado}.");
    }

    public function dashboardOperacional()
    {
        if (auth()->user()?->tipo === 'operador') {
            return redirect()->route('painel.operador');
        }

        $turno = $this->normalizarTurno(request('turno'));
        $data = request('data')
            ? Carbon::parse(request('data'))->toDateString()
            : Carbon::today()->toDateString();
        $base = Demanda::query()->where('possui_sobra', true);
        $base = $this->aplicarFiltrosOperacionais($base, $turno, $data);
        $separandoOutrasDatas = $this->getSeparandoDeOutrasDatas($turno, $data);
        $resumoOperacional = $this->getResumoOperacionalPorPeriodo($data, $data);

        $status = [
            'pendente' => (clone $base)->whereNull('separacao_iniciada_em')->count(),
            'em_separacao' => (clone $base)->whereNotNull('separacao_iniciada_em')->whereNull('separacao_finalizada_em')->count(),
            'finalizado_parcial' => (clone $base)->where('separacao_resultado', 'PARCIAL')->count(),
            'finalizado_completo' => (clone $base)->where('separacao_resultado', 'COMPLETA')->count(),
        ];

        $tempoDiffExpr = $this->tempoDiffMinExpr('separacao_iniciada_em', 'separacao_finalizada_em');

        $tempoMedioMin = (clone $base)
            ->whereNotNull('separacao_iniciada_em')
            ->whereNotNull('separacao_finalizada_em')
            ->selectRaw("AVG({$tempoDiffExpr}) as media")
            ->value('media');

        $ranking = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->groupBy('dd.separador_nome')
            ->selectRaw('dd.separador_nome as separador_nome')
            ->selectRaw('COUNT(*) as total_separacoes')
            ->selectRaw("AVG(" . $this->tempoDiffMinExpr('dd.created_at', 'dd.finalizado_em') . ") as tempo_medio_min")
            ->orderByDesc('total_separacoes')
            ->limit(10);

        if ($turno) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);
            $ranking->whereBetween('dd.finalizado_em', [$inicioTurno, $fimTurno]);
        } else {
            $ranking->whereDate('dd.finalizado_em', $data);
        }

        $ranking = $ranking->get();

        $comparativoTurno = collect(array_keys($this->turnosOperacionais()))->map(function ($nomeTurno) use ($data) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $nomeTurno);
            $q = Demanda::query()
                ->where('possui_sobra', true)
                ->whereNotNull('separacao_iniciada_em')
                ->whereNotNull('separacao_finalizada_em')
                ->whereBetween('separacao_finalizada_em', [$inicioTurno, $fimTurno]);

            $tempo = (clone $q)
                ->selectRaw("AVG(" . $this->tempoDiffMinExpr('separacao_iniciada_em', 'separacao_finalizada_em') . ") as media")
                ->value('media');

            return [
                'turno' => $this->turnosOperacionais()[$nomeTurno]['label'],
                'separacoes' => (clone $q)->count(),
                'tempo_medio' => $tempo ? round((float) $tempo, 1) : null,
            ];
        });

        $createdDateExpr = $this->dateExpr('created_at');
        $finalizedDateExpr = $this->dateExpr('separacao_finalizada_em');
        $dataBaseGraficos = Carbon::parse($data);
        $inicioEvolucao = $dataBaseGraficos->copy()->subDays(6)->startOfDay();
        $fimEvolucao = $dataBaseGraficos->copy()->endOfDay();
        $labels7 = collect(range(6, 0))->map(fn($d) => $dataBaseGraficos->copy()->subDays($d)->format('Y-m-d'));

        $finalizadasNoDia = Demanda::query()
            ->where('possui_sobra', true)
            ->whereNotNull('separacao_finalizada_em')
            ->when($turno, function ($q) use ($turno) {
                $this->aplicarFiltroTurnoSql($q, 'separacao_iniciada_em', $turno);
            })
            ->whereRaw("{$createdDateExpr} = {$finalizedDateExpr}")
            ->whereBetween('created_at', [$inicioEvolucao, $fimEvolucao])
            ->selectRaw("{$createdDateExpr} as dia")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $finalizadasOutroDia = Demanda::query()
            ->where('possui_sobra', true)
            ->whereNotNull('separacao_finalizada_em')
            ->when($turno, function ($q) use ($turno) {
                $this->aplicarFiltroTurnoSql($q, 'separacao_iniciada_em', $turno);
            })
            ->whereRaw("{$createdDateExpr} <> {$finalizedDateExpr}")
            ->whereBetween('created_at', [$inicioEvolucao, $fimEvolucao])
            ->selectRaw("{$createdDateExpr} as dia")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $mapFinalizadasNoDia = $finalizadasNoDia->pluck('total', 'dia');
        $mapFinalizadasOutroDia = $finalizadasOutroDia->pluck('total', 'dia');
        $seriesFinalizadasNoDia = $labels7->map(fn($dia) => (int) ($mapFinalizadasNoDia[$dia] ?? 0));
        $seriesFinalizadasOutroDia = $labels7->map(fn($dia) => (int) ($mapFinalizadasOutroDia[$dia] ?? 0));
        $apontamentosStretchPorHora = $this->getApontamentosStretchPorHora($data, $turno);
        $separacaoHoraOperador = $this->getSeparacaoHoraOperador($data, $turno);
        $projecaoProdutividade = app(DashboardService::class)->getProjecaoProdutividade($data);

        $dadosGraficos = [
            'projecaoProdutividade' => $projecaoProdutividade,
            'separacaoHoraOperador' => $separacaoHoraOperador,
            'producaoPicker' => $this->getProducaoPorPicker($data, $turno),
            'separacaoHoraOperadorDiaCompleto' => $this->getSeparacaoHoraOperadorDiaCompleto($data, $turno),
            'producaoPickerDiaCompleto' => $this->getProducaoPorPickerDiaCompleto($data, $turno),

            'status' => [
                'labels' => ['A separar', 'Separando', 'Finalizado parcial', 'Finalizado completo'],
                'values' => [
                    (int) $status['pendente'],
                    (int) $status['em_separacao'],
                    (int) $status['finalizado_parcial'],
                    (int) $status['finalizado_completo'],
                ],
            ],

            'turnos' => [
                'labels' => $comparativoTurno->pluck('turno')->values(),
                'values' => $comparativoTurno->pluck('separacoes')->map(fn($v) => (int) $v)->values(),
            ],

            'ranking' => [
                'labels' => $ranking->pluck('separador_nome')->values(),
                'values' => $ranking->pluck('total_separacoes')->map(fn($v) => (int) $v)->values(),
            ],

            'evolucao7' => [
                'labels' => $labels7->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values(),
                'finalizadas_no_dia' => $seriesFinalizadasNoDia->values(),
                'finalizadas_outro_dia' => $seriesFinalizadasOutroDia->values(),
            ],

            'stretchPorHora' => $apontamentosStretchPorHora,
        ];

        return view('demandas.dashboard_operacional', [
            'status' => $status,
            'tempoMedioMin' => $tempoMedioMin ? round((float) $tempoMedioMin, 1) : null,
            'ranking' => $ranking,
            'comparativoTurno' => $comparativoTurno,
            'turnoSelecionado' => $turno,
            'turnosOperacionais' => $this->turnosOperacionais(),
            'dataSelecionada' => $data,
            'separandoOutrasDatas' => $separandoOutrasDatas,
            'resumoOperacional' => $resumoOperacional,
            'dadosGraficos' => $dadosGraficos,
        ]);
    }

    private function getSeparacaoHoraOperador(string $data, ?string $turno): array
    {
        if (! Schema::hasTable('_tb_demanda_distribuicoes')) {
            return [
                'labels' => [],
                'datasets' => [],
                'total' => 0,
            ];
        }

        $inicioOperacao = Carbon::parse($data)->setTime(12, 0, 0);
        $fimOperacao = Carbon::parse($data)->endOfDay();

        if ($turno) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);

            $inicio = $inicioTurno->greaterThan($inicioOperacao)
                ? $inicioTurno->copy()
                : $inicioOperacao->copy();

            $fim = $fimTurno->lessThan($fimOperacao)
                ? $fimTurno->copy()
                : $fimOperacao->copy();
        } else {
            $inicio = $inicioOperacao->copy();
            $fim = $fimOperacao->copy();
        }

        if ($inicio->greaterThan($fim)) {
            return [
                'labels' => [],
                'datasets' => [],
                'total' => 0,
            ];
        }

        $labels = collect();
        $cursor = $inicio->copy()->startOfHour();

        while ($cursor <= $fim) {
            $labels->push($cursor->format('H:00'));
            $cursor->addHour();
        }

        $base = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicio, $fim]);

        $topOperadores = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as total')
            ->groupBy('operador')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $operadoresTop = $topOperadores->pluck('operador')->all();
        $operadoresVisiveis = $topOperadores->pluck('operador')->values();

        $temOutros = (clone $base)
            ->whereNotIn(DB::raw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador')"), $operadoresTop)
            ->exists();

        if ($temOutros) {
            $operadoresVisiveis->push('Outros');
        }

        $hourExpr = $this->hourExpr('dd.finalizado_em');

        $dados = (clone $base)
            ->selectRaw("{$hourExpr} as hora")
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as caixas')
            ->groupBy('hora', 'operador')
            ->orderBy('hora')
            ->get();

        $mapa = [];

        foreach ($dados as $item) {
            $hora = str_pad((string) $item->hora, 2, '0', STR_PAD_LEFT) . ':00';
            $operador = in_array($item->operador, $operadoresTop, true) ? $item->operador : 'Outros';
            $mapa[$operador][$hora] = ($mapa[$operador][$hora] ?? 0) + (int) $item->caixas;
        }

        $cores = [
            '#2563EB',
            '#0F766E',
            '#7C3AED',
            '#B45309',
            '#BE123C',
            '#0369A1',
            '#4D7C0F',
            '#475569',
            '#94A3B8',
        ];

        $datasets = $operadoresVisiveis->values()->map(function ($operador, $idx) use ($labels, $mapa, $cores) {
            return [
                'label' => $operador,
                'data' => $labels->map(fn($label) => (int) ($mapa[$operador][$label] ?? 0))->values(),
                'backgroundColor' => $cores[$idx % count($cores)],
                'borderRadius' => 4,
                'maxBarThickness' => 44,
            ];
        });

        return [
            'labels' => $labels->values(),
            'datasets' => $datasets->values(),
            'total' => (int) $dados->sum('caixas'),
        ];
    }

    private function getApontamentosStretchPorHora(string $data, ?string $turno): array
    {
        if (! Schema::hasTable('_tb_apontamentos_paletes_stretch')) {
            return [
                'labels' => [],
                'values' => [],
                'total' => 0,
            ];
        }

        if ($turno) {
            [$inicio, $fim] = $this->intervaloTurno($data, $turno);
        } else {
            $inicio = Carbon::parse($data)->startOfDay();
            $fim = Carbon::parse($data)->endOfDay();
        }

        $labels = collect();
        $cursor = $inicio->copy()->startOfHour();

        while ($cursor <= $fim) {
            $labels->push($cursor->format('H:00'));
            $cursor->addHour();
        }

        $hourExpr = $this->hourExpr('apontado_em_servidor');
        $dados = DB::table('_tb_apontamentos_paletes_stretch')
            ->whereNull('deleted_at')
            ->where('status', 'APONTADO')
            ->whereBetween('apontado_em_servidor', [$inicio, $fim])
            ->selectRaw("{$hourExpr} as hora")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->mapWithKeys(fn($item) => [str_pad((string) $item->hora, 2, '0', STR_PAD_LEFT) . ':00' => (int) $item->total]);

        $values = $labels->map(fn($label) => (int) ($dados[$label] ?? 0));

        return [
            'labels' => $labels->values(),
            'values' => $values->values(),
            'total' => (int) $values->sum(),
        ];
    }

    public function relatoriosOperacional()
    {
        if (auth()->user()?->tipo === 'operador') {
            return redirect()->route('painel.operador');
        }

        $base = Demanda::query()->where('possui_sobra', true);
        $total = (clone $base)->count();
        $parcial = (clone $base)->where('separacao_resultado', 'PARCIAL')->count();
        $completa = (clone $base)->where('separacao_resultado', 'COMPLETA')->count();
        $abertas = (clone $base)->whereNull('separacao_finalizada_em')->count();

        $tempoMedioMin = (clone $base)
            ->whereNotNull('separacao_iniciada_em')
            ->whereNotNull('separacao_finalizada_em')
            ->selectRaw("AVG(" . $this->tempoDiffMinExpr('separacao_iniciada_em', 'separacao_finalizada_em') . ") as media")
            ->value('media');

        return view('demandas.relatorios', [
            'total' => $total,
            'parcial' => $parcial,
            'completa' => $completa,
            'abertas' => $abertas,
            'tempoMedioMin' => $tempoMedioMin ? round((float) $tempoMedioMin, 1) : null,
        ]);
    }

    public function reportTurno(Request $request)
    {
        $data = $request->input('data', Carbon::today()->toDateString());
        $turno = $this->normalizarTurno($request->input('turno')) ?? $this->turnoAtual();
        $mensagem = trim((string) $request->input('mensagem', 'Bom dia!!!'));
        $turnos = $this->turnosOperacionais();
        [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);

        $baseDistribuicoes = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicioTurno, $fimTurno]);

        $separadores = (clone $baseDistribuicoes)
            ->whereNotNull('dd.separador_nome')
            ->whereRaw("TRIM(dd.separador_nome) <> ''")
            ->selectRaw('dd.separador_nome as separador')
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as pecas')
            ->selectRaw('SUM(COALESCE(dd.quantidade_skus, 0)) as skus')
            ->groupBy('dd.separador_nome')
            ->orderByDesc('pecas')
            ->get()
            ->map(function ($item) {
                return [
                    'separador' => mb_strtoupper($item->separador),
                    'pecas' => (int) $item->pecas,
                    'skus' => (int) $item->skus,
                ];
            });

        $totais = [
            'pecas' => (int) $separadores->sum('pecas'),
            'skus' => (int) $separadores->sum('skus'),
            'box' => (int) (clone $baseDistribuicoes)
                ->whereNotNull('d.stage')
                ->whereRaw("TRIM(d.stage) <> ''")
                ->distinct()
                ->count('d.stage'),
            'dt' => (int) (clone $baseDistribuicoes)
                ->distinct()
                ->count('d.id'),
        ];

        return view('demandas.report_turno', [
            'dataSelecionada' => $data,
            'turnoSelecionado' => $turno,
            'turnosOperacionais' => $turnos,
            'turnoAtual' => $turnos[$turno] ?? $turnos['T1'],
            'mensagem' => $mensagem !== '' ? $mensagem : 'Bom dia!!!',
            'inicioTurno' => $inicioTurno,
            'fimTurno' => $fimTurno,
            'separadores' => $separadores,
            'totais' => $totais,
        ]);
    }

    public function identificacaoA4(Request $request)
    {
        $dados = [
            'dt' => trim((string) $request->input('dt', '')),
            'pallets' => trim((string) $request->input('pallets', '')),
            'data' => $request->input('data', Carbon::today()->toDateString()),
            'conferente' => mb_strtoupper(trim((string) $request->input('conferente', ''))),
        ];

        if ($dados['dt'] !== '') {
            $demanda = Demanda::query()
                ->where('fo', $dados['dt'])
                ->orWhere('id', $dados['dt'])
                ->first();

            if ($demanda) {
                $dados['dt'] = (string) $demanda->fo;
            }
        }

        return view('demandas.identificacao_a4', [
            'dados' => $dados,
        ]);
    }

    private function aplicarFiltrosOperacionais($query, ?string $turno, ?string $data)
    {
        if ($data) {
            $query->whereDate('created_at', $data);
        }

        if ($turno) {
            $query->whereNotNull('separacao_iniciada_em');
            $this->aplicarFiltroTurnoSql($query, 'separacao_iniciada_em', $turno);
        }

        return $query;
    }

    private function getResumoOperacionalPorPeriodo(?string $dataInicio, ?string $dataFim): array
    {
        $query = Demanda::query();

        if ($dataInicio && $dataFim) {
            if ($dataInicio === $dataFim) {
                $query->whereDate('created_at', $dataInicio);
            } else {
                $query->whereBetween('created_at', [
                    Carbon::parse($dataInicio)->startOfDay(),
                    Carbon::parse($dataFim)->endOfDay(),
                ]);
            }
        }

        $totalGeradas = (clone $query)->count();
        $totalPicking = (clone $query)->where('possui_sobra', true)->count();
        $createdDateExpr = $this->dateExpr('created_at');
        $finalizedDateExpr = $this->dateExpr('separacao_finalizada_em');
        $finalizadasForaDataCriacao = (clone $query)
            ->where('possui_sobra', true)
            ->whereNotNull('separacao_finalizada_em')
            ->whereRaw("{$createdDateExpr} <> {$finalizedDateExpr}")
            ->count();

        return [
            'geradas' => $totalGeradas,
            'picking' => $totalPicking,
            'fora_picking' => max(0, $totalGeradas - $totalPicking),
            'finalizadas_fora_data_criacao' => $finalizadasForaDataCriacao,
        ];
    }

    private function getSeparandoDeOutrasDatas(?string $turno, string $data)
    {
        $dateExpr = $this->dateExpr('created_at');
        $query = Demanda::query()
            ->where('possui_sobra', true)
            ->whereDate('created_at', '<>', $data)
            ->whereNotNull('separacao_iniciada_em')
            ->whereNull('separacao_finalizada_em')
            ->selectRaw("{$dateExpr} as data_origem")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('data_origem')
            ->orderBy('data_origem');

        if ($turno) {
            $this->aplicarFiltroTurnoSql($query, 'separacao_iniciada_em', $turno);
        }

        return $query->get()->map(function ($item) {
            return [
                'data' => Carbon::parse($item->data_origem)->format('d/m/Y'),
                'total' => (int) $item->total,
            ];
        });
    }

    private function aplicarFiltroTurnoSql($query, string $colunaDataHora, ?string $turno): void
    {
        $turno = $this->normalizarTurno($turno);

        if ($turno === 'T1') {
            $query->whereRaw("TIME({$colunaDataHora}) BETWEEN ? AND ?", ['06:00:00', '13:59:59']);
            return;
        }

        if ($turno === 'T2') {
            $query->whereRaw("TIME({$colunaDataHora}) BETWEEN ? AND ?", ['14:00:00', '21:59:59']);
            return;
        }

        if ($turno === 'T3') {
            $query->where(function ($q) use ($colunaDataHora) {
                $q->whereRaw("TIME({$colunaDataHora}) BETWEEN ? AND ?", ['22:00:00', '23:59:59'])
                    ->orWhereRaw("TIME({$colunaDataHora}) BETWEEN ? AND ?", ['00:00:00', '05:59:59']);
            });
        }
    }

    private function turnosOperacionais(): array
    {
        return [
            'T1' => ['label' => 'Turno A (T1)', 'periodo' => '06h as 14h'],
            'T2' => ['label' => 'Turno B (T2)', 'periodo' => '14h as 22h'],
            'T3' => ['label' => 'Turno C (T3)', 'periodo' => '22h as 06h'],
        ];
    }

    private function normalizarTurno(?string $turno): ?string
    {
        return match (strtoupper((string) $turno)) {
            'T1', 'A', 'MANHA', 'MANHÃ' => 'T1',
            'T2', 'B', 'TARDE' => 'T2',
            'T3', 'C', 'NOITE' => 'T3',
            default => null,
        };
    }

    private function turnoAtual(): string
    {
        $hora = Carbon::now()->format('H:i:s');

        if ($hora >= '06:00:00' && $hora < '14:00:00') {
            return 'T1';
        }

        if ($hora >= '14:00:00' && $hora < '22:00:00') {
            return 'T2';
        }

        return 'T3';
    }

    private function intervaloTurno(string $data, string $turno): array
    {
        $dia = Carbon::parse($data);

        return match ($turno) {
            'T2' => [
                $dia->copy()->setTime(14, 0),
                $dia->copy()->setTime(21, 59, 59),
            ],
            'T3' => [
                $dia->copy()->setTime(22, 0),
                $dia->copy()->addDay()->setTime(5, 59, 59),
            ],
            default => [
                $dia->copy()->setTime(6, 0),
                $dia->copy()->setTime(13, 59, 59),
            ],
        };
    }

    private function tempoDiffMinExpr(string $colInicio, string $colFim): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "(julianday({$colFim}) - julianday({$colInicio})) * 1440";
        }

        return "TIMESTAMPDIFF(MINUTE, {$colInicio}, {$colFim})";
    }

    private function dateExpr(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        return $driver === 'sqlite' ? "date({$column})" : "DATE({$column})";
    }

    private function hourExpr(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        return $driver === 'sqlite' ? "strftime('%H', {$column})" : "HOUR({$column})";
    }

    private function getProducaoPorPicker(string $data, ?string $turno = null): array
    {
        if (! Schema::hasTable('_tb_demanda_distribuicoes')) {
            return [
                'labels' => [],
                'values' => [],
                'total' => 0,
            ];
        }

        $inicioOperacao = Carbon::parse($data)->setTime(12, 0, 0);
        $fimOperacao = Carbon::parse($data)->endOfDay();

        if ($turno) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);

            $inicio = $inicioTurno->greaterThan($inicioOperacao)
                ? $inicioTurno->copy()
                : $inicioOperacao->copy();

            $fim = $fimTurno->lessThan($fimOperacao)
                ? $fimTurno->copy()
                : $fimOperacao->copy();
        } else {
            $inicio = $inicioOperacao->copy();
            $fim = $fimOperacao->copy();
        }

        if ($inicio->greaterThan($fim)) {
            return [
                'labels' => [],
                'values' => [],
                'total' => 0,
            ];
        }

        $dados = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicio, $fim])
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as caixas')
            ->groupBy('operador')
            ->orderByDesc('caixas')
            ->limit(10)
            ->get();

        return [
            'labels' => $dados->pluck('operador')->values(),
            'values' => $dados->pluck('caixas')->map(fn($valor) => (int) $valor)->values(),
            'total' => (int) $dados->sum('caixas'),
        ];
    }

    private function getSeparacaoHoraOperadorDiaCompleto(string $data, ?string $turno): array
    {
        if (! Schema::hasTable('_tb_demanda_distribuicoes')) {
            return [
                'labels' => [],
                'datasets' => [],
                'total' => 0,
            ];
        }

        $inicioDia = Carbon::parse($data)->startOfDay();
        $fimDia = Carbon::parse($data)->endOfDay();

        if ($turno) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);

            $inicio = $inicioTurno->greaterThan($inicioDia) ? $inicioTurno->copy() : $inicioDia->copy();
            $fim = $fimTurno->lessThan($fimDia) ? $fimTurno->copy() : $fimDia->copy();
        } else {
            $inicio = $inicioDia->copy();
            $fim = $fimDia->copy();
        }

        if ($inicio->greaterThan($fim)) {
            return [
                'labels' => [],
                'datasets' => [],
                'total' => 0,
            ];
        }

        $labels = collect();
        $cursor = $inicio->copy()->startOfHour();

        while ($cursor <= $fim) {
            $labels->push($cursor->format('H:00'));
            $cursor->addHour();
        }

        $base = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicio, $fim]);

        $topOperadores = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as total')
            ->groupBy('operador')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $operadoresTop = $topOperadores->pluck('operador')->all();
        $operadoresVisiveis = $topOperadores->pluck('operador')->values();

        $temOutros = (clone $base)
            ->whereNotIn(DB::raw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador')"), $operadoresTop)
            ->exists();

        if ($temOutros) {
            $operadoresVisiveis->push('Outros');
        }

        $hourExpr = $this->hourExpr('dd.finalizado_em');

        $dados = (clone $base)
            ->selectRaw("{$hourExpr} as hora")
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as caixas')
            ->groupBy('hora', 'operador')
            ->orderBy('hora')
            ->get();

        $mapa = [];

        foreach ($dados as $item) {
            $hora = str_pad((string) $item->hora, 2, '0', STR_PAD_LEFT) . ':00';
            $operador = in_array($item->operador, $operadoresTop, true) ? $item->operador : 'Outros';
            $mapa[$operador][$hora] = ($mapa[$operador][$hora] ?? 0) + (int) $item->caixas;
        }

        $cores = [
            '#2563EB',
            '#0F766E',
            '#7C3AED',
            '#B45309',
            '#BE123C',
            '#0369A1',
            '#4D7C0F',
            '#475569',
            '#94A3B8',
        ];

        $datasets = $operadoresVisiveis->values()->map(function ($operador, $idx) use ($labels, $mapa, $cores) {
            return [
                'label' => $operador,
                'data' => $labels->map(fn($label) => (int) ($mapa[$operador][$label] ?? 0))->values(),
                'backgroundColor' => $cores[$idx % count($cores)],
                'borderRadius' => 4,
                'maxBarThickness' => 44,
            ];
        });

        return [
            'labels' => $labels->values(),
            'datasets' => $datasets->values(),
            'total' => (int) $dados->sum('caixas'),
        ];
    }

    private function getProducaoPorPickerDiaCompleto(string $data, ?string $turno = null): array
    {
        if (! Schema::hasTable('_tb_demanda_distribuicoes')) {
            return [
                'labels' => [],
                'values' => [],
                'total' => 0,
            ];
        }

        $inicioDia = Carbon::parse($data)->startOfDay();
        $fimDia = Carbon::parse($data)->endOfDay();

        if ($turno) {
            [$inicioTurno, $fimTurno] = $this->intervaloTurno($data, $turno);

            $inicio = $inicioTurno->greaterThan($inicioDia) ? $inicioTurno->copy() : $inicioDia->copy();
            $fim = $fimTurno->lessThan($fimDia) ? $fimTurno->copy() : $fimDia->copy();
        } else {
            $inicio = $inicioDia->copy();
            $fim = $fimDia->copy();
        }

        if ($inicio->greaterThan($fim)) {
            return [
                'labels' => [],
                'values' => [],
                'total' => 0,
            ];
        }

        $dados = DB::table('_tb_demanda_distribuicoes as dd')
            ->join('_tb_demanda as d', 'd.id', '=', 'dd.demanda_id')
            ->where('d.possui_sobra', true)
            ->whereNotNull('dd.finalizado_em')
            ->whereBetween('dd.finalizado_em', [$inicio, $fim])
            ->selectRaw("COALESCE(NULLIF(TRIM(dd.separador_nome), ''), 'Sem operador') as operador")
            ->selectRaw('SUM(COALESCE(dd.quantidade_pecas, 0)) as caixas')
            ->groupBy('operador')
            ->orderByDesc('caixas')
            ->limit(10)
            ->get();

        return [
            'labels' => $dados->pluck('operador')->values(),
            'values' => $dados->pluck('caixas')->map(fn($valor) => (int) $valor)->values(),
            'total' => (int) $dados->sum('caixas'),
        ];
    }
}
