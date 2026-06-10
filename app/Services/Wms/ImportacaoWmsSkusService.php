<?php

namespace App\Services\Wms;

use App\Models\Wms\WmsSku;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportacaoWmsSkusService
{
    public function __construct(private readonly WmsExcelReader $reader)
    {
    }

    public function importar(UploadedFile $arquivo): array
    {
        $linhas = $this->reader->ler($arquivo);
        [$indiceCabecalho, $cabecalho] = $this->reader->localizarCabecalho($linhas, ['ITEM']);

        if ($indiceCabecalho === null) {
            throw new \InvalidArgumentException('Não foi possível localizar o cabeçalho da base de SKUs. Verifique se existe a coluna ITEM.');
        }

        $resumo = $this->resumoBase($cabecalho);

        foreach ($linhas as $indice => $linha) {
            if ($indice <= $indiceCabecalho) {
                continue;
            }

            $resumo['total_lido']++;

            try {
                $dados = $this->reader->normalizarLinha($linha, $cabecalho);
                $sku = $this->normalizarTexto($this->reader->valor($dados, ['ITEM']));

                if ($sku === null) {
                    $resumo['total_ignorado']++;
                    continue;
                }

                $resultado = $this->salvarSku($sku, $dados);
                $resumo[$resultado]++;
            } catch (Throwable $e) {
                $resumo['erros_encontrados']++;
                $resumo['falhas'][] = [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ];

                Log::warning('Falha ao importar SKU WMS.', [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $resumo;
    }

    private function salvarSku(string $sku, array $dados): string
    {
        $cadastro = WmsSku::firstOrNew(['sku' => $sku]);
        $criado = ! $cadastro->exists;

        $this->preencherSemNulos($cadastro, [
            'peso_kg' => $this->decimal($this->reader->valor($dados, ['PESO ITEM [Kg]', 'PESO ITEM KG'])),
            'classe_peso' => $this->normalizarTexto($this->reader->valor($dados, ['CLASSE PESO'])),
            'classe_cubagem' => $this->normalizarTexto($this->reader->valor($dados, ['CLASSE CUBAGEM'])),
            'curva_abc' => $this->normalizarTexto($this->reader->valor($dados, ['CURVA'])),
            'ativo' => true,
        ]);

        $alterado = $cadastro->isDirty();
        $cadastro->save();

        if ($criado) {
            return 'total_criado';
        }

        return $alterado ? 'total_atualizado' : 'total_ignorado';
    }

    private function preencherSemNulos(WmsSku $sku, array $campos): void
    {
        foreach ($campos as $campo => $valor) {
            if ($valor === null) {
                continue;
            }

            $sku->{$campo} = $valor;
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
            'total_criado' => 0,
            'total_atualizado' => 0,
            'total_ignorado' => 0,
            'erros_encontrados' => 0,
            'falhas' => [],
            'colunas_detectadas' => array_values(array_filter($cabecalho)),
        ];
    }
}
