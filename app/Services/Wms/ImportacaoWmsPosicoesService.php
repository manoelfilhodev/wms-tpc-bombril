<?php

namespace App\Services\Wms;

use App\Models\Wms\WmsPosicao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportacaoWmsPosicoesService
{
    public function __construct(
        private readonly WmsExcelReader $reader,
        private readonly WmsRotaPickingService $rotaPicking
    )
    {
    }

    public function importar(UploadedFile $arquivo): array
    {
        $linhas = $this->reader->ler($arquivo);
        [$indiceCabecalho, $cabecalho] = $this->reader->localizarCabecalho($linhas, ['RUA', 'POSICAO']);

        if ($indiceCabecalho === null) {
            throw new \InvalidArgumentException('Não foi possível localizar o cabeçalho da base de posições. Verifique se existem as colunas RUA e POSICAO.');
        }

        $resumo = $this->resumoBase($cabecalho);
        $sequencia = 0;

        foreach ($linhas as $indice => $linha) {
            if ($indice <= $indiceCabecalho) {
                continue;
            }

            $resumo['total_lido']++;
            $sequencia++;

            try {
                $dados = $this->reader->normalizarLinha($linha, $cabecalho);
                $rua = $this->normalizarTexto($this->reader->valor($dados, ['RUA']));
                $posicao = $this->normalizarTexto($this->reader->valor($dados, ['POSICAO', 'POSIÇÃO']));

                if ($rua === null || $posicao === null) {
                    $resumo['total_ignorado']++;
                    continue;
                }

                $resultado = $this->salvarPosicao($dados, $rua, $posicao, $sequencia);
                $resumo[$resultado]++;
            } catch (Throwable $e) {
                $resumo['erros_encontrados']++;
                $resumo['falhas'][] = [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ];

                Log::warning('Falha ao importar posição WMS.', [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $resumo;
    }

    private function salvarPosicao(array $dados, string $rua, string $posicao, int $sequencia): string
    {
        $bloco = $this->normalizarTexto($this->reader->valor($dados, ['BLOCO']));
        $endereco = $this->normalizarTexto($this->reader->valor($dados, ['POSICAO2', 'POSICÃO 2', 'POSICAO FORMATADA', 'ENDERECO', 'ENDEREÇO']))
            ?? trim($rua . ' ' . $posicao);

        $cadastro = WmsPosicao::firstOrNew([
            'bloco' => $bloco,
            'rua' => $rua,
            'posicao' => $posicao,
            'endereco' => $endereco,
        ]);
        $criado = ! $cadastro->exists;

        $this->preencherSemNulos($cadastro, [
            'lado' => $this->normalizarTexto($this->reader->valor($dados, ['LADO'])),
            'sequencia_rota' => $this->rotaPicking->calcularSequenciaRota($rua, $posicao) ?? $sequencia,
            'status' => $this->normalizarTexto($this->reader->valor($dados, ['STATUS'])),
            'ativo' => true,
        ]);

        $alterado = $cadastro->isDirty();
        $cadastro->save();

        if ($criado) {
            return 'total_criado';
        }

        return $alterado ? 'total_atualizado' : 'total_ignorado';
    }

    private function preencherSemNulos(WmsPosicao $posicao, array $campos): void
    {
        foreach ($campos as $campo => $valor) {
            if ($valor === null) {
                continue;
            }

            $posicao->{$campo} = $valor;
        }
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
            'total_criado' => 0,
            'total_atualizado' => 0,
            'total_ignorado' => 0,
            'erros_encontrados' => 0,
            'falhas' => [],
            'colunas_detectadas' => array_values(array_filter($cabecalho)),
        ];
    }
}
