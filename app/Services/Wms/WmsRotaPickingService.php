<?php

namespace App\Services\Wms;

use App\Models\Wms\WmsPosicao;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WmsRotaPickingService
{
    private const ORDEM_RUAS = [
        'PA' => 1,
        'PB' => 2,
        'PC' => 3,
        'PD' => 4,
        'PE' => 5,
        'PF' => 6,
    ];

    public function ordemRua(?string $rua): int
    {
        return self::ORDEM_RUAS[strtoupper((string) $rua)] ?? 999;
    }

    public function calcularSequenciaRota(?string $rua, mixed $posicao): ?int
    {
        $ordemRua = $this->ordemRua($rua);
        $numeroPosicao = $this->numeroPosicao($posicao);

        if ($ordemRua === 999 || $numeroPosicao === null) {
            return null;
        }

        return (($ordemRua - 1) * 60) + $numeroPosicao;
    }

    public function janelaRota(mixed $posicao): int
    {
        $numeroPosicao = $this->numeroPosicao($posicao);

        if ($numeroPosicao === null) {
            return 999;
        }

        return (int) ceil($numeroPosicao / 20);
    }

    public function pesoOrdem(?string $classePeso): int
    {
        return match ($this->normalizar($classePeso)) {
            'PESADO' => 1,
            'MEDIO' => 2,
            'LEVE' => 3,
            default => 9,
        };
    }

    public function cubagemOrdem(?string $classeCubagem): int
    {
        return match ($this->normalizar($classeCubagem)) {
            'GRANDE' => 1,
            'MEDIO' => 2,
            'PEQUENO' => 3,
            default => 9,
        };
    }

    public function curvaOrdem(?string $curva): int
    {
        return match (strtoupper((string) $curva)) {
            'A' => 1,
            'B' => 2,
            'C' => 3,
            default => 9,
        };
    }

    public function ordenarSeparacaoInteligente(Collection $itens): Collection
    {
        return $itens->sortBy(fn (array $item) => [
            $item['rua_ordem'] ?? 999,
            $item['janela_rota'] ?? 999,
            $item['peso_ordem'] ?? 9,
            $item['cubagem_ordem'] ?? 9,
            $item['curva_ordem'] ?? 9,
            $item['sequencia_rota'] ?? PHP_INT_MAX,
            $item['sku'],
        ]);
    }

    public function atualizarSequenciasPosicoes(): array
    {
        $resumo = [
            'total' => 0,
            'atualizadas' => 0,
            'ignoradas' => 0,
        ];

        WmsPosicao::query()
            ->orderBy('id')
            ->chunkById(200, function (Collection $posicoes) use (&$resumo): void {
                foreach ($posicoes as $posicao) {
                    $resumo['total']++;
                    $sequencia = $this->calcularSequenciaRota($posicao->rua, $posicao->posicao);

                    if ($sequencia === null) {
                        $resumo['ignoradas']++;
                        continue;
                    }

                    if ((int) $posicao->sequencia_rota === $sequencia) {
                        continue;
                    }

                    $posicao->forceFill(['sequencia_rota' => $sequencia])->save();
                    $resumo['atualizadas']++;
                }
            });

        return $resumo;
    }

    public function numeroPosicao(mixed $posicao): ?int
    {
        if ($posicao === null || trim((string) $posicao) === '') {
            return null;
        }

        preg_match('/\d+/', (string) $posicao, $matches);

        if (! isset($matches[0])) {
            return null;
        }

        return (int) $matches[0];
    }

    private function normalizar(?string $valor): string
    {
        return Str::of((string) $valor)
            ->ascii()
            ->upper()
            ->trim()
            ->toString();
    }
}
