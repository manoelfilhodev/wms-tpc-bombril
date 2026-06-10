<?php

namespace App\Services\Wms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WmsExcelReader
{
    public function ler(UploadedFile $arquivo): array
    {
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());

        return $extensao === 'csv'
            ? $this->lerCsv($arquivo)
            : $this->lerPlanilha($arquivo);
    }

    private function lerPlanilha(UploadedFile $arquivo): array
    {
        $spreadsheet = IOFactory::load($arquivo->getPathname());

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    }

    private function lerCsv(UploadedFile $arquivo): array
    {
        $handle = fopen($arquivo->getPathname(), 'rb');

        if ($handle === false) {
            throw new \RuntimeException('Não foi possível abrir o CSV enviado.');
        }

        $linhas = [];
        $delimitador = $this->detectarDelimitador($handle);

        while (($linha = fgetcsv($handle, 0, $delimitador)) !== false) {
            if ($linha === [null] || $linha === []) {
                continue;
            }

            $linhas[] = array_combine(range(0, count($linha) - 1), $linha);
        }

        fclose($handle);

        return $linhas;
    }

    private function detectarDelimitador($handle): string
    {
        $primeiraLinha = fgets($handle) ?: '';
        rewind($handle);

        $candidatos = [',', ';', "\t"];
        $pontuacoes = [];

        foreach ($candidatos as $delimitador) {
            $pontuacoes[$delimitador] = substr_count($primeiraLinha, $delimitador);
        }

        arsort($pontuacoes);

        return (string) array_key_first($pontuacoes);
    }

    public function localizarCabecalho(array $linhas, array $colunasObrigatorias): array
    {
        $obrigatorias = array_map(fn (string $coluna) => $this->normalizarChave($coluna), $colunasObrigatorias);

        foreach (array_slice($linhas, 0, 20, true) as $indice => $linha) {
            $valores = array_map(fn ($valor) => trim((string) $valor), $linha);
            $normalizados = array_map(fn ($valor) => $this->normalizarChave($valor), $valores);

            if (count(array_intersect($obrigatorias, $normalizados)) === count($obrigatorias)) {
                return [$indice, $valores];
            }
        }

        return [null, []];
    }

    public function normalizarLinha(array $linha, array $cabecalho): array
    {
        $dados = [];
        $ocorrencias = [];

        foreach ($cabecalho as $coluna => $nome) {
            if ($this->vazio($nome)) {
                continue;
            }

            $chave = $this->normalizarChave((string) $nome);
            $ocorrencias[$chave] = ($ocorrencias[$chave] ?? 0) + 1;

            if ($ocorrencias[$chave] > 1) {
                $chave .= $ocorrencias[$chave];
            }

            $dados[$chave] = Arr::get($linha, $coluna);
        }

        return $dados;
    }

    public function valor(array $dados, array $chaves): mixed
    {
        foreach ($chaves as $chave) {
            $normalizada = $this->normalizarChave($chave);

            if (array_key_exists($normalizada, $dados) && ! $this->vazio($dados[$normalizada])) {
                return is_string($dados[$normalizada]) ? trim($dados[$normalizada]) : $dados[$normalizada];
            }
        }

        return null;
    }

    public function normalizarChave(string $chave): string
    {
        $semAcento = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chave);
        $normalizada = strtolower((string) $semAcento);

        return preg_replace('/[^a-z0-9]+/', '', $normalizada) ?? '';
    }

    public function vazio(mixed $valor): bool
    {
        return $valor === null || trim((string) $valor) === '';
    }
}
