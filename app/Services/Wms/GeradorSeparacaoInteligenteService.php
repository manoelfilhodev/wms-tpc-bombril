<?php

namespace App\Services\Wms;

use App\Models\Demanda;
use App\Models\DemandaItem;
use App\Models\Wms\WmsSeparacaoFolha;
use App\Models\Wms\WmsSeparacaoGeracao;
use App\Models\Wms\WmsSeparacaoItem;
use App\Models\Wms\WmsSku;
use App\Models\Wms\WmsSkuPosicao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeradorSeparacaoInteligenteService
{
    public const AGRUPAMENTO_UNICA = 'folha_unica';
    public const AGRUPAMENTO_RUA = 'rua';
    public const AGRUPAMENTO_CURVA = 'curva_abc';
    public const AGRUPAMENTO_SKU = 'sku';

    public const ORDENACAO_SKU = 'sku';
    public const ORDENACAO_CURVA = 'curva_abc';
    public const ORDENACAO_ENDERECO = 'endereco';
    public const ORDENACAO_ROTA = 'sequencia_rota';
    public const ORDENACAO_INTELIGENTE = 'inteligente';
    public const ORDENACAO_INTELIGENTE_RECOMENDADA = 'inteligente_recomendada';

    public const EQUALIZACAO_NAO = 'nao';
    public const EQUALIZACAO_SKUS = 'skus';
    public const EQUALIZACAO_QUANTIDADE = 'quantidade';
    public const EQUALIZACAO_RUAS = 'ruas';
    public const EQUALIZACAO_INTELIGENTE = 'inteligente';

    public function __construct(private readonly WmsRotaPickingService $rotaPicking)
    {
    }

    public function gerar(Demanda $demanda, array $configuracao, ?int $usuarioId = null): WmsSeparacaoGeracao
    {
        $agrupamento = $configuracao['criterio_agrupamento'] ?? self::AGRUPAMENTO_UNICA;
        $ordenacao = $configuracao['criterio_ordenacao'] ?? self::ORDENACAO_INTELIGENTE_RECOMENDADA;
        $equalizacao = $configuracao['criterio_equalizacao'] ?? self::EQUALIZACAO_NAO;
        $quantidadeSeparadores = max(1, (int) ($configuracao['quantidade_separadores'] ?? 1));

        return DB::transaction(function () use ($demanda, $agrupamento, $ordenacao, $equalizacao, $quantidadeSeparadores, $usuarioId): WmsSeparacaoGeracao {
            $itens = $this->montarItens($demanda);
            $folhasAgrupadas = $this->agruparItens($itens, $agrupamento, $ordenacao);
            $folhasOrdenadas = $this->ordenarFolhas($folhasAgrupadas, $ordenacao);
            $separadores = $this->equalizarFolhas($folhasOrdenadas, $equalizacao, $quantidadeSeparadores);

            $geracao = WmsSeparacaoGeracao::create([
                'demanda_id' => $demanda->id,
                'fo' => $demanda->fo,
                'criterio_agrupamento' => $agrupamento,
                'criterio_ordenacao' => $ordenacao,
                'criterio_equalizacao' => $equalizacao,
                'quantidade_separadores' => $quantidadeSeparadores,
                'total_itens' => $itens->count(),
                'total_skus' => $itens->pluck('sku')->unique()->count(),
                'total_ruas' => $itens->pluck('rua')->filter()->unique()->count(),
                'itens_sem_endereco' => $itens->filter(fn (array $item) => $item['endereco'] === null)->count(),
                'status' => 'GERADA',
                'gerado_por' => $usuarioId,
            ]);

            $numeroFolha = 1;

            foreach ($folhasOrdenadas as $folhaDados) {
                $itensDaFolha = $this->ordenarItens($folhaDados['itens'], $ordenacao)->values();
                $separadorNumero = $separadores[$folhaDados['chave']] ?? 1;

                $folha = WmsSeparacaoFolha::create([
                    'geracao_id' => $geracao->id,
                    'numero_folha' => $numeroFolha,
                    'separador_numero' => $separadorNumero,
                    'titulo' => $folhaDados['titulo'],
                    'rua' => $folhaDados['rua'],
                    'curva_abc' => $folhaDados['curva_abc'],
                    'total_skus' => $itensDaFolha->pluck('sku')->unique()->count(),
                    'total_quantidade' => $itensDaFolha->sum('quantidade'),
                    'peso_estimado' => $itensDaFolha->sum('peso_estimado'),
                    'status' => 'GERADA',
                ]);

                foreach ($itensDaFolha as $indice => $item) {
                    WmsSeparacaoItem::create(array_merge($this->camposPersistiveisItem($item), [
                        'geracao_id' => $geracao->id,
                        'folha_id' => $folha->id,
                        'ordem_separacao' => $indice + 1,
                    ]));
                }

                $numeroFolha++;
            }

            return $geracao->load(['folhas.itens', 'itens']);
        });
    }

    private function camposPersistiveisItem(array $item): array
    {
        return collect($item)->only([
            'demanda_id',
            'fo',
            'sku_id',
            'posicao_id',
            'sku',
            'descricao',
            'curva_abc',
            'rua',
            'posicao',
            'endereco',
            'lado',
            'quantidade',
            'sequencia_rota',
            'status',
            'observacao',
        ])->all();
    }

    private function montarItens(Demanda $demanda): Collection
    {
        return DemandaItem::query()
            ->where('demanda_id', $demanda->id)
            ->where('sobra', '>', 0)
            ->where('bloqueado', false)
            ->orderBy('sku_normalizado')
            ->get()
            ->unique(fn (DemandaItem $item) => implode('|', [
                $item->sku_normalizado ?: ltrim($item->sku, '0'),
                (string) $item->descricao,
                number_format((float) $item->sobra, 3, '.', ''),
            ]))
            ->groupBy(fn (DemandaItem $item) => ($item->sku_normalizado ?: ltrim($item->sku, '0')) . '|' . (string) $item->descricao)
            ->map(function (Collection $itensAgrupados): DemandaItem {
                $primeiro = $itensAgrupados->first();
                $primeiro->sobra = $itensAgrupados->sum(fn (DemandaItem $item) => (float) $item->sobra);

                return $primeiro;
            })
            ->values()
            ->map(fn (DemandaItem $item) => $this->montarItem($demanda, $item));
    }

    private function montarItem(Demanda $demanda, DemandaItem $item): array
    {
        $skuBusca = $item->sku_normalizado ?: ltrim($item->sku, '0');
        $skuWms = WmsSku::where('sku', $skuBusca)->first();
        $vinculo = $skuWms ? $this->melhorVinculo($skuWms->id) : null;
        $posicao = $vinculo?->posicao;
        $observacao = null;

        if (! $skuWms) {
            $observacao = 'SKU NÃO CADASTRADO';
        } elseif (! $posicao) {
            $observacao = 'SEM ENDEREÇO';
        }

        $quantidade = (float) $item->sobra;

        return [
            'demanda_id' => $demanda->id,
            'fo' => $demanda->fo,
            'sku_id' => $skuWms?->id,
            'posicao_id' => $posicao?->id,
            'sku' => $skuBusca,
            'descricao' => $item->descricao,
            'curva_abc' => $skuWms?->curva_abc,
            'rua' => $posicao?->rua,
            'posicao' => $posicao?->posicao,
            'endereco' => $posicao?->endereco,
            'lado' => $posicao?->lado,
            'quantidade' => $quantidade,
            'sequencia_rota' => $posicao?->sequencia_rota,
            'rua_ordem' => $this->rotaPicking->ordemRua($posicao?->rua),
            'janela_rota' => $this->rotaPicking->janelaRota($posicao?->posicao),
            'peso_ordem' => $this->rotaPicking->pesoOrdem($skuWms?->classe_peso),
            'cubagem_ordem' => $this->rotaPicking->cubagemOrdem($skuWms?->classe_cubagem),
            'curva_ordem' => $this->rotaPicking->curvaOrdem($skuWms?->curva_abc),
            'status' => $observacao ? 'INCONSISTENTE' : 'PENDENTE',
            'observacao' => $observacao,
            'peso_estimado' => $skuWms?->peso_kg !== null ? ((float) $skuWms->peso_kg * $quantidade) : 0,
        ];
    }

    private function melhorVinculo(int $skuId): ?WmsSkuPosicao
    {
        return WmsSkuPosicao::query()
            ->with('posicao')
            ->join('_tb_wms_posicoes as posicoes', 'posicoes.id', '=', '_tb_wms_sku_posicoes.posicao_id')
            ->where('_tb_wms_sku_posicoes.sku_id', $skuId)
            ->where('_tb_wms_sku_posicoes.ativo', true)
            ->where('posicoes.ativo', true)
            ->select('_tb_wms_sku_posicoes.*')
            ->orderByRaw('posicoes.sequencia_rota IS NULL')
            ->orderBy('posicoes.sequencia_rota')
            ->orderBy('posicoes.rua')
            ->orderBy('posicoes.posicao')
            ->first()
            ?? WmsSkuPosicao::query()
                ->with('posicao')
                ->join('_tb_wms_posicoes as posicoes', 'posicoes.id', '=', '_tb_wms_sku_posicoes.posicao_id')
                ->where('_tb_wms_sku_posicoes.sku_id', $skuId)
                ->select('_tb_wms_sku_posicoes.*')
                ->orderBy('posicoes.rua')
                ->orderBy('posicoes.posicao')
                ->first();
    }

    private function agruparItens(Collection $itens, string $criterio, string $ordenacao): Collection
    {
        if ($ordenacao === self::ORDENACAO_INTELIGENTE_RECOMENDADA) {
            $semEndereco = $itens->filter(fn (array $item) => $item['endereco'] === null)->values();
            $comEndereco = $itens->reject(fn (array $item) => $item['endereco'] === null);
            $grupos = $this->agruparItensBase($comEndereco, $criterio);

            if ($semEndereco->isNotEmpty()) {
                $grupos->push([
                    'chave' => 'inconsistencias_sem_endereco',
                    'titulo' => 'Inconsistências / Sem Endereço',
                    'rua' => 'SEM ENDEREÇO',
                    'curva_abc' => null,
                    'itens' => $semEndereco,
                    'inconsistencia' => true,
                ]);
            }

            return $grupos->values();
        }

        return $this->agruparItensBase($itens, $criterio);
    }

    private function agruparItensBase(Collection $itens, string $criterio): Collection
    {
        return $itens
            ->groupBy(fn (array $item) => $this->chaveGrupo($item, $criterio))
            ->map(fn (Collection $grupo, string $chave) => [
                'chave' => $chave,
                'titulo' => $this->tituloGrupo($grupo->first(), $criterio),
                'rua' => $criterio === self::AGRUPAMENTO_RUA ? ($grupo->first()['rua'] ?? 'SEM ENDEREÇO') : null,
                'curva_abc' => $criterio === self::AGRUPAMENTO_CURVA ? ($grupo->first()['curva_abc'] ?? 'SEM CURVA') : null,
                'itens' => $grupo->values(),
                'inconsistencia' => $grupo->every(fn (array $item) => $item['endereco'] === null),
            ])
            ->values();
    }

    private function chaveGrupo(array $item, string $criterio): string
    {
        return match ($criterio) {
            self::AGRUPAMENTO_RUA => 'rua:' . ($item['rua'] ?? 'SEM ENDERECO'),
            self::AGRUPAMENTO_CURVA => 'curva:' . ($item['curva_abc'] ?? 'SEM CURVA'),
            self::AGRUPAMENTO_SKU => 'sku:' . $item['sku'],
            default => 'unica',
        };
    }

    private function tituloGrupo(array $item, string $criterio): string
    {
        return match ($criterio) {
            self::AGRUPAMENTO_RUA => 'Rua ' . ($item['rua'] ?? 'SEM ENDEREÇO'),
            self::AGRUPAMENTO_CURVA => 'Curva ' . ($item['curva_abc'] ?? 'SEM CURVA'),
            self::AGRUPAMENTO_SKU => 'SKU ' . $item['sku'],
            default => 'Folha Única',
        };
    }

    private function ordenarItens(Collection $itens, string $criterio): Collection
    {
        return match ($criterio) {
            self::ORDENACAO_INTELIGENTE_RECOMENDADA => $this->rotaPicking->ordenarSeparacaoInteligente($itens),
            self::ORDENACAO_SKU => $itens->sortBy(fn (array $item) => $item['sku']),
            self::ORDENACAO_CURVA => $itens->sortBy(fn (array $item) => [$this->ordemCurva($item['curva_abc']), $item['sku']]),
            self::ORDENACAO_ENDERECO => $itens->sortBy(fn (array $item) => [$item['rua'] ?? 'ZZZ', $item['posicao'] ?? 'ZZZ', $item['endereco'] ?? 'ZZZ']),
            self::ORDENACAO_ROTA => $itens->sortBy(fn (array $item) => [$item['sequencia_rota'] ?? PHP_INT_MAX, $item['rua'] ?? 'ZZZ', $item['posicao'] ?? 'ZZZ']),
            default => $itens->sortBy(fn (array $item) => [$item['sequencia_rota'] ?? PHP_INT_MAX, $item['rua'] ?? 'ZZZ', $item['posicao'] ?? 'ZZZ']),
        };
    }

    private function ordemCurva(?string $curva): int
    {
        return match (strtoupper((string) $curva)) {
            'A' => 1,
            'B' => 2,
            'C' => 3,
            default => 9,
        };
    }

    private function ordenarFolhas(Collection $folhas, string $ordenacao): Collection
    {
        if ($ordenacao === self::ORDENACAO_INTELIGENTE_RECOMENDADA) {
            return $folhas
                ->sortBy(fn (array $folha) => [
                    $folha['inconsistencia'] ? 1 : 0,
                    $folha['itens']->min('rua_ordem') ?? 999,
                    $folha['itens']->min('janela_rota') ?? 999,
                    $folha['itens']->min('sequencia_rota') ?? PHP_INT_MAX,
                ])
                ->values();
        }

        return $this->ordenarFolhasPorVolume($folhas);
    }

    private function ordenarFolhasPorVolume(Collection $folhas): Collection
    {
        return $folhas
            ->sortByDesc(fn (array $folha) => $folha['itens']->sum('quantidade'))
            ->values();
    }

    private function equalizarFolhas(Collection $folhas, string $criterio, int $quantidadeSeparadores): array
    {
        if ($criterio === self::EQUALIZACAO_NAO || $quantidadeSeparadores <= 1) {
            return $folhas->mapWithKeys(fn (array $folha) => [$folha['chave'] => 1])->all();
        }

        $cargas = array_fill(1, $quantidadeSeparadores, 0.0);
        $atribuicoes = [];

        foreach ($folhas as $folha) {
            $separador = array_search(min($cargas), $cargas, true);
            $atribuicoes[$folha['chave']] = $separador;
            $cargas[$separador] += $this->cargaFolha($folha, $criterio);
        }

        return $atribuicoes;
    }

    private function cargaFolha(array $folha, string $criterio): float
    {
        $itens = $folha['itens'];

        return match ($criterio) {
            self::EQUALIZACAO_SKUS => (float) $itens->pluck('sku')->unique()->count(),
            self::EQUALIZACAO_RUAS => (float) $itens->pluck('rua')->filter()->unique()->count(),
            self::EQUALIZACAO_INTELIGENTE => (float) ($itens->sum('quantidade') + $itens->pluck('sku')->unique()->count() + $itens->pluck('rua')->filter()->unique()->count()),
            default => (float) $itens->sum('quantidade'),
        };
    }
}
