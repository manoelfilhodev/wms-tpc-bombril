<?php

namespace App\Services;

use App\Models\ClienteTransitTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportacaoClientesTransitTimeService
{
    public function importar(UploadedFile $arquivo): array
    {
        $linhas = strtolower((string) $arquivo->getClientOriginalExtension()) === 'csv'
            ? $this->lerCsv($arquivo)
            : $this->lerPlanilha($arquivo);

        return $this->processarLinhas($linhas);
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

        $pontuacoes = [];
        foreach ([',', ';', "\t"] as $delimitador) {
            $pontuacoes[$delimitador] = substr_count($primeiraLinha, $delimitador);
        }

        arsort($pontuacoes);

        return (string) array_key_first($pontuacoes);
    }

    private function processarLinhas(array $linhas): array
    {
        $resumo = [
            'total_lidas' => 0,
            'criadas' => 0,
            'atualizadas' => 0,
            'ignoradas' => 0,
            'erros' => 0,
            'falhas' => [],
        ];

        [$indiceCabecalho, $cabecalho] = $this->localizarCabecalho($linhas);

        if ($indiceCabecalho === null) {
            throw new \InvalidArgumentException('Não foi possível localizar o cabeçalho. Inclua a coluna Cliente ou codigo_cliente.');
        }

        foreach ($linhas as $indice => $linha) {
            if ($indice <= $indiceCabecalho) {
                continue;
            }

            $resumo['total_lidas']++;

            try {
                $dados = $this->normalizarLinha($linha, $cabecalho);
                $payload = $this->payload($dados);

                if ($payload === null) {
                    $resumo['ignoradas']++;
                    continue;
                }

                $cliente = ClienteTransitTime::firstOrNew(['codigo_cliente' => $payload['codigo_cliente']]);
                $criado = ! $cliente->exists;
                $cliente->fill($payload);
                $cliente->save();

                $resumo[$criado ? 'criadas' : 'atualizadas']++;
            } catch (\Throwable $e) {
                $resumo['erros']++;
                $resumo['falhas'][] = [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ];
            }
        }

        return $resumo;
    }

    private function localizarCabecalho(array $linhas): array
    {
        foreach (array_slice($linhas, 0, 20, true) as $indice => $linha) {
            $normalizados = array_map(fn ($valor) => $this->normalizarChave((string) $valor), $linha);

            if (
                in_array('codigocliente', $normalizados, true)
                || (
                    in_array('cliente', $normalizados, true)
                    && (
                        in_array('diascargapaletefechado', $normalizados, true)
                        || in_array('diascargafechado', $normalizados, true)
                        || in_array('transittimefechadadias', $normalizados, true)
                    )
                )
            ) {
                return [$indice, array_map(fn ($valor) => trim((string) $valor), $linha)];
            }
        }

        return [null, []];
    }

    private function normalizarLinha(array $linha, array $cabecalho): array
    {
        $dados = [];

        foreach ($cabecalho as $coluna => $nome) {
            if (trim((string) $nome) === '') {
                continue;
            }

            $dados[$this->normalizarChave((string) $nome)] = Arr::get($linha, $coluna);
        }

        return $dados;
    }

    private function payload(array $dados): ?array
    {
        $codigo = $this->valor($dados, ['codigo cliente', 'codigo_cliente', 'cod cliente', 'cod_cliente', 'cliente']);
        $nome = $this->valor($dados, ['nome cliente', 'nome_cliente', 'razao social', 'razão social']);
        $fechada = $this->valor($dados, [
            'transit time fechada dias',
            'transit_time_fechada_dias',
            'fechada dias',
            'dias carga fechado',
            'dias carga fechada',
            'dias carga palete fechado',
            'dias_carga_palete_fechado',
        ]);
        $fracionada = $this->valor($dados, [
            'transit time fracionada dias',
            'transit_time_fracionada_dias',
            'fracionada dias',
            'dias carga fracionado',
            'dias carga fracionada',
            'dias_carga_fracionado',
        ]);

        if ($this->vazio($codigo) && $this->vazio($fechada) && $this->vazio($fracionada)) {
            return null;
        }

        if ($this->vazio($codigo) || ! is_numeric($fechada) || ! is_numeric($fracionada)) {
            throw new \InvalidArgumentException('Cliente/código e dias de carga inteiros são obrigatórios.');
        }

        $payload = [
            'codigo_cliente' => trim((string) $codigo),
            'nome_cliente' => $this->textoOpcional($nome),
            'zona_partida' => $this->textoOpcional($this->valor($dados, ['zona partida', 'zona_partida'])),
            'regiao' => $this->textoOpcional($this->valor($dados, ['regiao', 'região'])),
            'uf' => $this->uf($this->valor($dados, ['uf', 'estado'])),
            'cidade' => $this->textoOpcional($this->valor($dados, ['cidade', 'cidade destino', 'cidade_destino', 'municipio', 'município'])),
            'zona_transporte' => $this->textoOpcional($this->valor($dados, ['zona transporte', 'zona_transporte'])),
            'ciclo_inte' => $this->inteiroOpcional($this->valor($dados, ['ciclo inte', 'ciclo_inte'])),
            'transit_time_fechada_dias' => (int) $fechada,
            'transit_time_fracionada_dias' => (int) $fracionada,
        ];

        $ativo = $this->valor($dados, ['ativo', 'status']);

        if (! $this->vazio($ativo)) {
            $payload['ativo'] = $this->ativo($ativo);
        }

        return $payload;
    }

    private function valor(array $dados, array $chaves): mixed
    {
        foreach ($chaves as $chave) {
            $valor = Arr::get($dados, $this->normalizarChave($chave));

            if (! $this->vazio($valor)) {
                return $valor;
            }
        }

        return null;
    }

    private function textoOpcional(mixed $valor): ?string
    {
        return $this->vazio($valor) ? null : trim((string) $valor);
    }

    private function uf(mixed $valor): ?string
    {
        return $this->vazio($valor) ? null : strtoupper(substr(trim((string) $valor), 0, 2));
    }

    private function inteiroOpcional(mixed $valor): ?int
    {
        return $this->vazio($valor) || ! is_numeric($valor) ? null : (int) $valor;
    }

    private function ativo(mixed $valor): bool
    {
        if ($this->vazio($valor)) {
            return true;
        }

        return in_array(strtolower(trim((string) $valor)), ['1', 'sim', 's', 'true', 'ativo', 'a'], true);
    }

    private function vazio(mixed $valor): bool
    {
        return $valor === null || trim((string) $valor) === '';
    }

    private function normalizarChave(string $chave): string
    {
        $chave = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chave) ?: $chave;

        return preg_replace('/[^a-z0-9]/', '', strtolower($chave)) ?: '';
    }
}
