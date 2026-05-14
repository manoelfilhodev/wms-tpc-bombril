<?php

namespace App\Services\Expedicao;

use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ImportacaoProgramacaoExpedicaoService
{
    private const ABA_PROG = 'PROG';

    public function importar(UploadedFile $arquivo): array
    {
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());

        if ($extensao === 'xlsb') {
            throw new \InvalidArgumentException(
                'Arquivos .xlsb ainda não são lidos pelo PhpSpreadsheet instalado. Abra o arquivo BASE PROG CRITERIOS INDICADOR.xlsb no Excel/LibreOffice e salve a aba PROG como .xlsx ou .csv para importar com segurança.'
            );
        }

        $linhas = $extensao === 'csv'
            ? $this->lerCsv($arquivo)
            : $this->lerPlanilha($arquivo);

        return $this->processarLinhas($linhas);
    }

    private function lerPlanilha(UploadedFile $arquivo): array
    {
        $spreadsheet = IOFactory::load($arquivo->getPathname());
        $sheet = $spreadsheet->getSheetByName(self::ABA_PROG) ?? $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, true);
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

    private function processarLinhas(array $linhas): array
    {
        $resumo = [
            'total_lidas' => 0,
            'criadas' => 0,
            'atualizadas' => 0,
            'ignoradas' => 0,
            'erros' => 0,
            'falhas' => [],
            'colunas_detectadas' => [],
        ];

        [$indiceCabecalho, $cabecalho] = $this->localizarCabecalho($linhas);

        if ($indiceCabecalho === null || $cabecalho === []) {
            throw new \InvalidArgumentException('Não foi possível localizar o cabeçalho da aba PROG. Verifique se existe a coluna Doc. Transporte.');
        }

        $resumo['colunas_detectadas'] = array_values(array_filter($cabecalho));

        foreach ($linhas as $indice => $linha) {
            if ($indice <= $indiceCabecalho) {
                continue;
            }

            $resumo['total_lidas']++;

            try {
                $dados = $this->normalizarLinha($linha, $cabecalho);
                $fo = $this->valor($dados, ['doc transporte', 'documento transporte', 'transporte', 'fo', 'dt sap']);

                if ($this->vazio($fo)) {
                    $resumo['ignoradas']++;
                    continue;
                }

                $resultado = $this->salvarLinha(trim((string) $fo), $dados);
                $resumo[$resultado]++;
            } catch (Throwable $e) {
                $resumo['erros']++;
                $resumo['falhas'][] = [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ];

                Log::warning('Falha ao importar linha da programação de expedição.', [
                    'linha' => $indice + 1,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $resumo;
    }

    private function localizarCabecalho(array $linhas): array
    {
        foreach (array_slice($linhas, 0, 20, true) as $indice => $linha) {
            $valores = array_map(fn ($valor) => trim((string) $valor), $linha);
            $normalizados = array_map(fn ($valor) => $this->normalizarChave($valor), $valores);

            if (in_array('doctransporte', $normalizados, true) || in_array('transporte', $normalizados, true)) {
                return [$indice, $valores];
            }
        }

        return [null, []];
    }

    private function normalizarLinha(array $linha, array $cabecalho): array
    {
        $dados = [];

        foreach ($cabecalho as $coluna => $nome) {
            if ($this->vazio($nome)) {
                continue;
            }

            $dados[$this->normalizarChave((string) $nome)] = Arr::get($linha, $coluna);
        }

        return $dados;
    }

    private function salvarLinha(string $fo, array $dados): string
    {
        $programacao = ExpedicaoProgramacao::firstOrNew(['fo' => $fo]);
        $criado = ! $programacao->exists;

        $camposProgramacao = [
            'dt_sap' => $fo,
            'agenda_entrega_em' => $this->combinarDataHora(
                $this->valor($dados, ['data agendamento', 'agenda entrega data']),
                $this->valor($dados, ['hora agendamento', 'agenda entrega hora'])
            ),
            'cidade_destino' => $this->valor($dados, ['cidade de destino', 'cidade destino', 'destino', 'cidade']),
            'uf_destino' => $this->uf($this->valor($dados, ['uf de destino', 'uf destino', 'estado'])),
            'cliente' => $this->valor($dados, ['desc cliente', 'cliente', 'nome']),
            'transportadora' => $this->valor($dados, ['transportadora']),
            'tipo_veiculo' => $this->valor($dados, ['tipo do veiculo', 'desc tp veiculo', 'tipo veiculo']),
            'tipo_carga' => $this->tipoCarga($dados),
            'observacoes' => $this->valor($dados, ['observacao', 'observações', 'observacoes']),
        ];

        $this->preencherSemNulos($programacao, $camposProgramacao);

        if ($criado && $this->vazio($programacao->status_previsao)) {
            $programacao->status_previsao = 'AGUARDANDO_EXPLOSAO';
        }

        $programacaoAlterada = $programacao->isDirty();
        $programacao->save();

        $demandaAlterada = $this->atualizarDemanda($fo, $dados);
        $this->recalcularPrevisao($programacao);

        if ($criado) {
            return 'criadas';
        }

        return $programacaoAlterada || $demandaAlterada ? 'atualizadas' : 'ignoradas';
    }

    private function atualizarDemanda(string $fo, array $dados): bool
    {
        $demanda = Demanda::where('fo', $fo)->first();

        if (! $demanda) {
            return false;
        }

        $dataAgendamento = $this->valor($dados, ['data agendamento']);
        $horaAgendamento = $this->valor($dados, ['hora agendamento']);
        $dataEntrada = $this->valor($dados, ['data entrada']);
        $horaEntrada = $this->valor($dados, ['hora entrada', 'entrada']);
        $dataSaida = $this->valor($dados, ['data saida', 'data saída']);
        $horaSaida = $this->valor($dados, ['hora saida', 'hora saída', 'saida', 'saída']);

        $camposDemanda = [
            'cliente' => $this->valor($dados, ['desc cliente', 'cliente', 'nome']),
            'transportadora' => $this->valor($dados, ['transportadora']),
            'doca' => $this->valor($dados, ['doca logistica', 'doca logística', 'doca']),
            'hora_agendada' => $this->hora($horaAgendamento),
            'entrada' => $this->hora($horaEntrada),
            'saida' => $this->hora($horaSaida),
            'conferencia_finalizada_em' => $this->combinarDataHora(
                $this->valor($dados, ['data validacao', 'data validação', 'data valida']),
                $this->valor($dados, ['hora validacao', 'hora validação', 'hora valida'])
            ),
            'separacao_iniciada_em' => $this->dataHora($this->valor($dados, ['separacao', 'separação']), $dataAgendamento),
            'carregamento_iniciado_em' => $this->dataHora($this->valor($dados, ['carregamento']), $dataAgendamento),
            'carregamento_finalizado_em' => $this->combinarDataHora($dataSaida, $horaSaida),
        ];

        if ($this->vazio($camposDemanda['conferencia_finalizada_em'])) {
            $camposDemanda['conferencia_finalizada_em'] = $this->dataHora(
                $this->valor($dados, ['validacao', 'validação', 'valida']),
                $dataAgendamento
            );
        }

        if ($this->vazio($camposDemanda['carregamento_finalizado_em'])) {
            $camposDemanda['carregamento_finalizado_em'] = $this->dataHora(
                $this->valor($dados, ['saida', 'saída']),
                $dataAgendamento
            );
        }

        $this->preencherSemNulos($demanda, $camposDemanda);

        if (! $demanda->isDirty()) {
            return false;
        }

        $demanda->save();

        return true;
    }

    private function recalcularPrevisao(ExpedicaoProgramacao $programacao): void
    {
        try {
            app(PrevisaoExpedicaoService::class)->calcular($programacao->id);
        } catch (Throwable $e) {
            Log::warning('Falha ao recalcular previsão da programação importada.', [
                'fo' => $programacao->fo,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function preencherSemNulos($model, array $campos): void
    {
        foreach ($campos as $campo => $valor) {
            if ($this->vazio($valor)) {
                continue;
            }

            $model->{$campo} = is_string($valor) ? trim($valor) : $valor;
        }
    }

    private function valor(array $dados, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            $chave = $this->normalizarChave($alias);

            if (array_key_exists($chave, $dados) && ! $this->vazio($dados[$chave])) {
                return $dados[$chave];
            }
        }

        return null;
    }

    private function valorMesmoVazio(array $dados, array $aliases): array
    {
        foreach ($aliases as $alias) {
            $chave = $this->normalizarChave($alias);

            if (array_key_exists($chave, $dados)) {
                return [true, $dados[$chave]];
            }
        }

        return [false, null];
    }

    private function tipoCarga(array $dados): ?string
    {
        [$existePalletChep, $palletChep] = $this->valorMesmoVazio($dados, ['pallet chep', 'palete chep']);

        if ($existePalletChep) {
            return strtoupper(trim((string) $palletChep)) === 'X'
                ? 'PALETIZADA'
                : 'GRANEL';
        }

        return $this->valor($dados, ['tipo expedicao', 'tipo expedição', 'expedicao', 'expedição']);
    }

    private function dataHora(mixed $valor, mixed $dataPadrao = null): ?Carbon
    {
        if ($this->vazio($valor)) {
            return null;
        }

        if (is_numeric($valor)) {
            $dateTime = ExcelDate::excelToDateTimeObject((float) $valor);

            if ((float) $valor < 1 && ! $this->vazio($dataPadrao)) {
                return $this->combinarDataHora($dataPadrao, $valor);
            }

            return Carbon::instance($dateTime);
        }

        $texto = trim((string) $valor);

        $dataPlanilha = $this->dataPlanilhaAmericana($texto);

        if ($dataPlanilha) {
            return $dataPlanilha;
        }

        if (preg_match('/^\d{1,2}:\d{2}/', $texto) && ! $this->vazio($dataPadrao)) {
            return $this->combinarDataHora($dataPadrao, $texto);
        }

        try {
            return Carbon::parse(str_replace('/', '-', $texto));
        } catch (Throwable) {
            return null;
        }
    }

    private function combinarDataHora(mixed $data, mixed $hora): ?Carbon
    {
        if ($this->vazio($data) && $this->vazio($hora)) {
            return null;
        }

        $dataCarbon = $this->dataHora($data);

        if (! $dataCarbon) {
            return $this->dataHora($hora);
        }

        $horaTexto = $this->hora($hora);

        if ($horaTexto) {
            [$horas, $minutos, $segundos] = array_pad(explode(':', $horaTexto), 3, 0);
            $dataCarbon->setTime((int) $horas, (int) $minutos, (int) $segundos);
        }

        return $dataCarbon;
    }

    private function dataPlanilhaAmericana(string $valor): ?Carbon
    {
        $valor = trim($valor);

        $formatos = [
            '!m/d/Y h:i:s A',
            '!m/d/Y h:i A',
            '!m/d/Y H:i:s',
            '!m/d/Y H:i',
            '!m/d/Y',
            '!n/j/Y h:i:s A',
            '!n/j/Y h:i A',
            '!n/j/Y H:i:s',
            '!n/j/Y H:i',
            '!n/j/Y',
        ];

        foreach ($formatos as $formato) {
            try {
                $data = Carbon::createFromFormat($formato, $valor);

                if ($data !== false && $data->format('Y') !== '1970') {
                    return $data;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function hora(mixed $valor): ?string
    {
        if ($this->vazio($valor)) {
            return null;
        }

        if (is_numeric($valor)) {
            $dateTime = ExcelDate::excelToDateTimeObject((float) $valor);

            return Carbon::instance($dateTime)->format('H:i:s');
        }

        $texto = trim((string) $valor);

        foreach (['h:i:s A', 'h:i A', 'g:i:s A', 'g:i A'] as $formato) {
            try {
                $hora = Carbon::createFromFormat($formato, strtoupper($texto));

                if ($hora !== false) {
                    return $hora->format('H:i:s');
                }
            } catch (Throwable) {
                continue;
            }
        }

        if (preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', $texto, $matches)) {
            return sprintf('%02d:%02d:%02d', (int) $matches[1], (int) $matches[2], (int) ($matches[3] ?? 0));
        }

        return null;
    }

    private function uf(mixed $valor): ?string
    {
        if ($this->vazio($valor)) {
            return null;
        }

        $uf = strtoupper(trim((string) $valor));

        return strlen($uf) === 2 ? $uf : null;
    }

    private function vazio(mixed $valor): bool
    {
        return $valor === null || trim((string) $valor) === '';
    }

    private function normalizarChave(string $valor): string
    {
        $valor = trim($valor);
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) ?: $valor;
        $valor = strtolower($valor);

        return preg_replace('/[^a-z0-9]+/', '', $valor) ?? '';
    }
}
