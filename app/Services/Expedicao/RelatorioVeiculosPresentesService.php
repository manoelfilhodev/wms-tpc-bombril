<?php

namespace App\Services\Expedicao;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use ZipArchive;

class RelatorioVeiculosPresentesService
{
    public function resumo(Collection $programacoes): array
    {
        $linhas = $this->linhasPorFo();
        $agora = now();

        $programacoes->each(function ($programacao) use ($linhas, $agora) {
            $linha = $linhas->get((string) $programacao->fo)
                ?? $linhas->get((string) $programacao->dt_sap);

            $saidaPrevista = $programacao->ultimaPrevisao?->previsao_saida_caminhao;
            $saidaPrevista = $saidaPrevista ? Carbon::parse($saidaPrevista) : null;
            $dataAgenda = $programacao->agenda_entrega_em
                ? Carbon::parse($programacao->agenda_entrega_em)->startOfDay()
                : null;
            $dataExpedicao = $programacao->data_expedicao_em
                ?? data_get($programacao, 'demanda.created_at')
                ?? $programacao->created_at;
            $agendaVencida = $dataAgenda
                && (empty($dataExpedicao) || ! $dataAgenda->isSameDay(Carbon::parse($dataExpedicao)))
                && $dataAgenda->lt($agora->copy()->startOfDay());
            $naPlanta = (bool) data_get($linha, 'na_planta', false);
            $jaSaiu = (bool) data_get($linha, 'ja_saiu', false);
            $atrasadoSemPresenca = $agendaVencida
                && $saidaPrevista
                && $saidaPrevista->lt($agora)
                && ! $naPlanta
                && ! $jaSaiu;

            $programacao->presenca_veiculo = [
                'encontrado' => $linha !== null,
                'na_planta' => $naPlanta,
                'ja_saiu' => $jaSaiu,
                'atrasado_sem_presenca' => $atrasadoSemPresenca,
                'placa' => data_get($linha, 'placa'),
                'motorista' => data_get($linha, 'motorista'),
                'entrada_em' => data_get($linha, 'entrada_em'),
                'saida_em' => data_get($linha, 'saida_em'),
            ];
        });

        $veiculosNaPlanta = $linhas
            ->filter(fn (array $linha) => $linha['na_planta'])
            ->values();

        $veiculosSaidos = $linhas
            ->filter(fn (array $linha) => $linha['ja_saiu'])
            ->values();

        $atrasadosSemPresenca = $programacoes
            ->filter(fn ($programacao) => (bool) data_get($programacao->presenca_veiculo, 'atrasado_sem_presenca'))
            ->values();

        return [
            'arquivo_encontrado' => $this->arquivoRelatorio() !== null,
            'arquivo_atual' => $this->arquivoAtual(),
            'total_relatorio' => $linhas->count(),
            'na_planta' => [
                'total' => $veiculosNaPlanta->count(),
                'itens' => $veiculosNaPlanta->take(8)->values(),
            ],
            'saidos' => [
                'total' => $veiculosSaidos->count(),
            ],
            'atrasados_sem_presenca' => [
                'total' => $atrasadosSemPresenca->count(),
                'itens' => $atrasadosSemPresenca
                    ->sortBy(fn ($programacao) => optional($programacao->ultimaPrevisao?->previsao_saida_caminhao)->timestamp ?? PHP_INT_MAX)
                    ->take(8)
                    ->map(fn ($programacao) => [
                        'fo' => $programacao->fo,
                        'dt' => $programacao->dt_sap ?: $programacao->fo,
                        'destino' => trim(($programacao->cidade_destino ?? '-') . '/' . ($programacao->uf_destino ?? '-'), '/'),
                        'saida_prevista' => optional($programacao->ultimaPrevisao?->previsao_saida_caminhao)->format('d/m H:i'),
                    ])
                    ->values(),
            ],
        ];
    }

    public function importar(UploadedFile $arquivo): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new InvalidArgumentException('A extensão PHP ZipArchive não está disponível neste ambiente.');
        }

        $linhas = $this->linhasPorFo($arquivo->getRealPath());

        if ($linhas->isEmpty()) {
            throw new InvalidArgumentException('Nenhuma linha válida foi encontrada. Confira se a planilha possui a coluna Doc Transporte.');
        }

        $destino = storage_path('app/relatorio-saida-dts-veiculos-presentes.xlsx');
        $arquivo->move(dirname($destino), basename($destino));

        return $this->resumoImportacao($linhas, $destino);
    }

    public function arquivoAtual(): ?array
    {
        $arquivo = $this->arquivoRelatorio();

        if (! $arquivo) {
            return null;
        }

        return [
            'nome' => basename($arquivo),
            'caminho' => $arquivo,
            'atualizado_em' => Carbon::createFromTimestamp(filemtime($arquivo))->format('d/m/Y H:i'),
            'tamanho_kb' => round(filesize($arquivo) / 1024, 1),
        ];
    }

    private function linhasPorFo(?string $arquivo = null): Collection
    {
        $arquivo = $arquivo ?: $this->arquivoRelatorio();

        if (! $arquivo || ! class_exists(ZipArchive::class)) {
            return collect();
        }

        $zip = new ZipArchive();

        if ($zip->open($arquivo) !== true) {
            return collect();
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXml) {
            return collect();
        }

        $sheet = simplexml_load_string($sheetXml);

        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return collect();
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $linha = [];

            foreach ($row->c as $cell) {
                $linha[$this->indiceColuna((string) $cell['r'])] = $this->valorCelula($cell, $sharedStrings);
            }

            ksort($linha);
            $rows[] = $linha;
        }

        if (count($rows) < 2) {
            return collect();
        }

        $cabecalhos = collect($rows[0])
            ->mapWithKeys(fn ($valor, $indice) => [$this->normalizarCabecalho((string) $valor) => $indice]);

        return collect(array_slice($rows, 1))
            ->map(function (array $row) use ($cabecalhos) {
                $fo = trim((string) $this->valorPorCabecalho($row, $cabecalhos, 'doc transporte'));

                if ($fo === '') {
                    return null;
                }

                $entradaEm = $this->dataHoraExcel(
                    $this->valorPorCabecalho($row, $cabecalhos, 'data entrada'),
                    $this->valorPorCabecalho($row, $cabecalhos, 'hora entrada')
                );
                $saidaEm = $this->dataHoraExcel(
                    $this->valorPorCabecalho($row, $cabecalhos, 'data saida'),
                    $this->valorPorCabecalho($row, $cabecalhos, 'hora saida')
                );

                return [
                    'fo' => $fo,
                    'placa' => trim((string) $this->valorPorCabecalho($row, $cabecalhos, 'placa veiculo')),
                    'motorista' => trim((string) $this->valorPorCabecalho($row, $cabecalhos, 'motorista')),
                    'entrada_em' => $entradaEm,
                    'saida_em' => $saidaEm,
                    'na_planta' => $entradaEm !== null && $saidaEm === null,
                    'ja_saiu' => $saidaEm !== null,
                ];
            })
            ->filter()
            ->keyBy('fo');
    }

    private function arquivoRelatorio(): ?string
    {
        $candidatos = collect([
            storage_path('app/relatorio-saida-dts-veiculos-presentes.xlsx'),
            base_path('relatorio-saida-dts-veiculos-presentes.xlsx'),
            base_path('relatório-saida-dts-veiculos-presentes.xlsx'),
        ]);

        $glob = collect(glob(storage_path('app/*saida*dts*veiculos*presentes*.xlsx')) ?: [])
            ->merge(glob(base_path('*saida*dts*veiculos*presentes*.xlsx')) ?: [])
            ->merge(glob(base_path('node_modules/*saida*dts*veiculos*presentes*.xlsx')) ?: [])
            ->unique()
            ->values();

        return $candidatos
            ->merge($glob)
            ->first(fn (string $path) => is_file($path));
    }

    private function resumoImportacao(Collection $linhas, string $arquivo): array
    {
        $naPlanta = $linhas->filter(fn (array $linha) => $linha['na_planta'])->count();
        $saidos = $linhas->filter(fn (array $linha) => $linha['ja_saiu'])->count();

        return [
            'arquivo' => basename($arquivo),
            'total_lidas' => $linhas->count(),
            'na_planta' => $naPlanta,
            'saidos' => $saidos,
            'sem_movimento' => max(0, $linhas->count() - $naPlanta - $saidos),
            'amostra' => $linhas->take(8)->values()->map(fn (array $linha) => [
                'fo' => $linha['fo'],
                'placa' => $linha['placa'] ?: '-',
                'motorista' => $linha['motorista'] ?: '-',
                'entrada_em' => $linha['entrada_em']?->format('d/m/Y H:i'),
                'saida_em' => $linha['saida_em']?->format('d/m/Y H:i'),
            ])->all(),
        ];
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! $xml) {
            return [];
        }

        $sharedXml = simplexml_load_string($xml);
        $strings = [];

        foreach ($sharedXml->si ?? [] as $item) {
            $texto = [];

            if (isset($item->t)) {
                $texto[] = (string) $item->t;
            }

            foreach ($item->r ?? [] as $fragmento) {
                $texto[] = (string) $fragmento->t;
            }

            $strings[] = implode('', $texto);
        }

        return $strings;
    }

    private function valorCelula($cell, array $sharedStrings): string
    {
        $tipo = (string) ($cell['t'] ?? '');
        $valor = (string) ($cell->v ?? '');

        if ($tipo === 's') {
            return $sharedStrings[(int) $valor] ?? '';
        }

        if ($tipo === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        return $valor;
    }

    private function valorPorCabecalho(array $row, Collection $cabecalhos, string $cabecalho): ?string
    {
        $indice = $cabecalhos->get($this->normalizarCabecalho($cabecalho));

        return $indice === null ? null : ($row[$indice] ?? null);
    }

    private function dataHoraExcel($data, $hora): ?Carbon
    {
        $dataOriginal = trim((string) $data);
        $horaOriginal = trim((string) $hora);

        if ($dataOriginal === '') {
            return null;
        }

        if (! is_numeric(str_replace(',', '.', $dataOriginal))) {
            try {
                $dataHora = trim($dataOriginal . ' ' . $horaOriginal);

                return Carbon::parse($dataHora);
            } catch (\Throwable) {
                return null;
            }
        }

        $data = (float) str_replace(',', '.', $dataOriginal);
        $hora = (float) str_replace(',', '.', $horaOriginal);

        if ($data <= 0) {
            return null;
        }

        return Carbon::create(1899, 12, 30)
            ->startOfDay()
            ->addDays((int) floor($data))
            ->addSeconds((int) round($hora * 86400));
    }

    private function indiceColuna(string $referencia): int
    {
        preg_match('/^[A-Z]+/', $referencia, $matches);
        $letras = $matches[0] ?? 'A';
        $indice = 0;

        for ($i = 0; $i < strlen($letras); $i++) {
            $indice = $indice * 26 + ord($letras[$i]) - 64;
        }

        return $indice - 1;
    }

    private function normalizarCabecalho(string $valor): string
    {
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($valor)) ?: $valor;
        $valor = strtolower($valor);

        return preg_replace('/[^a-z0-9]+/', ' ', $valor) ?: '';
    }
}
