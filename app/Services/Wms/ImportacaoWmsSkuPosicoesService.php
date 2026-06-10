<?php

namespace App\Services\Wms;

use App\Models\Wms\WmsPosicao;
use App\Models\Wms\WmsSku;
use App\Models\Wms\WmsSkuPosicao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportacaoWmsSkuPosicoesService
{
    public function __construct(private readonly WmsExcelReader $reader)
    {
    }

    public function importar(UploadedFile $arquivo): array
    {
        $linhas = $this->reader->ler($arquivo);
        [$indiceCabecalho, $cabecalho] = $this->reader->localizarCabecalho($linhas, ['RUA', 'POSICAO', 'SKU']);

        if ($indiceCabecalho === null) {
            throw new \InvalidArgumentException('Não foi possível localizar o cabeçalho da base de vínculos. Verifique se existem as colunas RUA, POSICAO e SKU.');
        }

        $resumo = $this->resumoBase($cabecalho);

        foreach ($linhas as $indice => $linha) {
            if ($indice <= $indiceCabecalho) {
                continue;
            }

            $resumo['total_lido']++;

            try {
                $dados = $this->reader->normalizarLinha($linha, $cabecalho);
                $skuCodigo = $this->normalizarTexto($this->reader->valor($dados, ['SKU']));

                if ($skuCodigo === null) {
                    $resumo['linhas_ignoradas_sem_sku']++;
                    continue;
                }

                $sku = WmsSku::where('sku', $skuCodigo)->first();

                if (! $sku) {
                    $resumo['skus_nao_encontrados']++;
                    $resumo['falhas'][] = [
                        'linha' => $indice + 1,
                        'erro' => "SKU {$skuCodigo} não encontrado.",
                    ];
                    continue;
                }

                $posicao = $this->localizarPosicao($dados);

                if (! $posicao) {
                    $resumo['posicoes_nao_encontradas']++;
                    $resumo['falhas'][] = [
                        'linha' => $indice + 1,
                        'erro' => 'Posição não encontrada para rua/posição/endereço informados.',
                    ];
                    continue;
                }

                $resultado = $this->salvarVinculo($sku, $posicao, $skuCodigo, $dados);
                $resumo[$resultado]++;
            } catch (Throwable $e) {
                $resumo['erros']++;
                $resumo['falhas'][] = [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ];

                Log::warning('Falha ao importar vínculo SKU x posição WMS.', [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $resumo;
    }

    private function localizarPosicao(array $dados): ?WmsPosicao
    {
        $bloco = $this->normalizarTexto($this->reader->valor($dados, ['BLOCO']));
        $rua = $this->normalizarTexto($this->reader->valor($dados, ['RUA']));
        $posicaoPlanilha = $this->normalizarTexto($this->reader->valor($dados, ['POSICAO', 'POSIÇÃO']));
        $endereco = $this->normalizarTexto($this->reader->valor($dados, ['POSICAO2', 'POSICÃO 2', 'POSICAO FORMATADA', 'ENDERECO', 'ENDEREÇO']));

        if ($rua === null || $posicaoPlanilha === null) {
            return null;
        }

        if ($endereco === null && $this->pareceEnderecoCompleto($posicaoPlanilha, $rua)) {
            $endereco = $posicaoPlanilha;
        }

        if ($endereco !== null) {
            $porEndereco = WmsPosicao::query()
                ->where('rua', $rua)
                ->where('endereco', $endereco);

            $bloco === null
                ? $porEndereco->whereNull('bloco')
                : $porEndereco->where('bloco', $bloco);

            $posicaoEncontrada = $porEndereco->first()
                ?? WmsPosicao::query()
                    ->where('rua', $rua)
                    ->where('endereco', $endereco)
                    ->first();

            if ($posicaoEncontrada) {
                return $posicaoEncontrada;
            }
        }

        $endereco ??= trim($rua . ' ' . $posicaoPlanilha);

        $query = WmsPosicao::query()
            ->where('rua', $rua)
            ->where('posicao', $posicaoPlanilha)
            ->where('endereco', $endereco);

        $bloco === null
            ? $query->whereNull('bloco')
            : $query->where('bloco', $bloco);

        $posicaoEncontrada = $query->first();

        if ($posicaoEncontrada) {
            return $posicaoEncontrada;
        }

        return WmsPosicao::query()
            ->where('rua', $rua)
            ->where('posicao', $posicaoPlanilha)
            ->where('endereco', $endereco)
            ->first();
    }

    private function pareceEnderecoCompleto(string $valor, string $rua): bool
    {
        return str_contains($valor, ' ') || str_starts_with(strtoupper($valor), strtoupper($rua));
    }

    private function salvarVinculo(WmsSku $sku, WmsPosicao $posicao, string $skuCodigo, array $dados): string
    {
        $vinculo = WmsSkuPosicao::firstOrNew([
            'sku_id' => $sku->id,
            'posicao_id' => $posicao->id,
        ]);
        $criado = ! $vinculo->exists;

        $this->preencherSemNulos($vinculo, [
            'sku' => $skuCodigo,
            'endereco' => $this->normalizarTexto($this->reader->valor($dados, ['POSICAO2', 'POSICÃO 2', 'POSICAO FORMATADA', 'ENDERECO', 'ENDEREÇO'])) ?? $posicao->endereco,
            'qtd_padrao' => $this->decimal($this->reader->valor($dados, ['QTD', 'QUANTIDADE'])),
            'prioridade' => $posicao->sequencia_rota,
            'ativo' => true,
        ]);

        $alterado = $vinculo->isDirty();
        $vinculo->save();

        if ($criado) {
            return 'vinculos_criados';
        }

        return $alterado ? 'vinculos_atualizados' : 'linhas_ignoradas_sem_alteracao';
    }

    private function preencherSemNulos(WmsSkuPosicao $vinculo, array $campos): void
    {
        foreach ($campos as $campo => $valor) {
            if ($valor === null) {
                continue;
            }

            $vinculo->{$campo} = $valor;
        }
    }

    private function decimal(mixed $valor): ?float
    {
        if ($this->reader->vazio($valor)) {
            return null;
        }

        $normalizado = str_replace(',', '.', preg_replace('/[^\d,.-]/', '', (string) $valor) ?? '');

        return is_numeric($normalizado) ? (float) $normalizado : null;
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        if ($this->reader->vazio($valor)) {
            return null;
        }

        return trim((string) $valor);
    }

    private function resumoBase(array $cabecalho): array
    {
        return [
            'total_lido' => 0,
            'vinculos_criados' => 0,
            'vinculos_atualizados' => 0,
            'linhas_ignoradas_sem_sku' => 0,
            'linhas_ignoradas_sem_alteracao' => 0,
            'skus_nao_encontrados' => 0,
            'posicoes_nao_encontradas' => 0,
            'erros' => 0,
            'falhas' => [],
            'colunas_detectadas' => array_values(array_filter($cabecalho)),
        ];
    }
}
