<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Services\SystemLogService;
use App\Services\Wms\ImportacaoWmsPosicoesService;
use App\Services\Wms\ImportacaoWmsSkusService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class WmsImportacaoController extends Controller
{
    public function index()
    {
        return view('wms.importacoes.index');
    }

    public function skus(Request $request, ImportacaoWmsSkusService $service)
    {
        $this->validarArquivo($request, 'Selecione a base de SKUs.');

        try {
            $resumo = $service->importar($request->file('arquivo'));

            SystemLogService::record([
                'module' => 'wms',
                'action' => 'importacao_wms_skus_realizada',
                'description' => 'Usuário importou a base mestre de SKUs WMS.',
                'entity_type' => 'wms_sku',
                'new_values' => [
                    'arquivo' => $request->file('arquivo')?->getClientOriginalName(),
                    'resumo' => $resumo,
                ],
            ]);

            return back()
                ->with('success', 'Importação de SKUs concluída.')
                ->with('wms_importacao_resumo', $resumo)
                ->with('wms_importacao_tipo', 'SKUs');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function posicoes(Request $request, ImportacaoWmsPosicoesService $service)
    {
        $this->validarArquivo($request, 'Selecione a base de posições.');

        try {
            $resumo = $service->importar($request->file('arquivo'));

            SystemLogService::record([
                'module' => 'wms',
                'action' => 'importacao_wms_posicoes_realizada',
                'description' => 'Usuário importou a base mestre de posições de picking WMS.',
                'entity_type' => 'wms_posicao',
                'new_values' => [
                    'arquivo' => $request->file('arquivo')?->getClientOriginalName(),
                    'resumo' => $resumo,
                ],
            ]);

            return back()
                ->with('success', 'Importação de posições concluída.')
                ->with('wms_importacao_resumo', $resumo)
                ->with('wms_importacao_tipo', 'Posições');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function validarArquivo(Request $request, string $mensagemRequired): void
    {
        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $extensoesPermitidas = ['xlsx', 'xls', 'csv'];
                    $mimesPermitidos = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'application/vnd.ms-office',
                        'application/zip',
                        'application/octet-stream',
                        'text/csv',
                        'text/plain',
                    ];

                    $extensao = strtolower((string) $value->getClientOriginalExtension());
                    $mime = (string) $value->getMimeType();

                    if (! in_array($extensao, $extensoesPermitidas, true)) {
                        $fail('O arquivo deve estar nos formatos .xlsx, .xls ou .csv.');
                    }

                    if (! in_array($mime, $mimesPermitidos, true)) {
                        $fail('O MIME do arquivo enviado não é permitido para importação WMS.');
                    }
                },
            ],
        ], [
            'arquivo.required' => $mensagemRequired,
            'arquivo.file' => 'O upload enviado não é um arquivo válido.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);
    }
}
