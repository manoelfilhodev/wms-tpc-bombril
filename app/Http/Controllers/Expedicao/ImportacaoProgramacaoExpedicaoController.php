<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Services\Expedicao\ImportacaoProgramacaoExpedicaoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportacaoProgramacaoExpedicaoController extends Controller
{
    public function index()
    {
        return view('expedicao.importacao-programacao.index');
    }

    public function store(Request $request, ImportacaoProgramacaoExpedicaoService $service)
    {
        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $extensoesPermitidas = ['xlsx', 'xls', 'csv', 'xlsb'];
                    $mimesPermitidos = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'application/vnd.ms-office',
                        'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                        'application/zip',
                        'application/octet-stream',
                        'text/csv',
                        'text/plain',
                    ];

                    $extensao = strtolower((string) $value->getClientOriginalExtension());
                    $mime = (string) $value->getMimeType();

                    if (! in_array($extensao, $extensoesPermitidas, true)) {
                        $fail('O arquivo deve estar nos formatos .xlsx, .xls, .csv ou .xlsb.');
                    }

                    if (! in_array($mime, $mimesPermitidos, true)) {
                        $fail('O MIME do arquivo enviado não é permitido para importação de programação.');
                    }
                },
            ],
        ], [
            'arquivo.required' => 'Selecione o arquivo da programação.',
            'arquivo.file' => 'O upload enviado não é um arquivo válido.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);

        try {
            $resumo = $service->importar($request->file('arquivo'));

            return back()
                ->with('success', 'Importação da programação concluída.')
                ->with('importacao_programacao_resumo', $resumo);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
