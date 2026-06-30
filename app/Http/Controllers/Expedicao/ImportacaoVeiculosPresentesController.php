<?php

namespace App\Http\Controllers\Expedicao;

use App\Http\Controllers\Controller;
use App\Services\Expedicao\RelatorioVeiculosPresentesService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportacaoVeiculosPresentesController extends Controller
{
    public function index(RelatorioVeiculosPresentesService $service)
    {
        return view('expedicao.importacao-veiculos-presentes.index', [
            'arquivoAtual' => $service->arquivoAtual(),
        ]);
    }

    public function store(Request $request, RelatorioVeiculosPresentesService $service)
    {
        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $extensao = strtolower((string) $value->getClientOriginalExtension());
                    $mime = (string) $value->getMimeType();
                    $mimesPermitidos = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                        'application/octet-stream',
                    ];

                    if ($extensao !== 'xlsx') {
                        $fail('O relatório de veículos presentes deve estar no formato .xlsx.');
                    }

                    if (! in_array($mime, $mimesPermitidos, true)) {
                        $fail('O MIME do arquivo enviado não é permitido para esta importação.');
                    }
                },
            ],
        ], [
            'arquivo.required' => 'Selecione o relatório de saída de DTs/veículos presentes.',
            'arquivo.file' => 'O upload enviado não é um arquivo válido.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);

        try {
            $resumo = $service->importar($request->file('arquivo'));

            SystemLogService::record([
                'module' => 'importacao',
                'action' => 'importacao_veiculos_presentes_realizada',
                'description' => 'Usuário importou o relatório de saída de DTs/veículos presentes.',
                'entity_type' => 'expedicao_veiculos_presentes',
                'new_values' => [
                    'arquivo' => $request->file('arquivo')?->getClientOriginalName(),
                    'resumo' => $resumo,
                ],
            ]);

            return back()
                ->with('success', 'Relatório de veículos presentes importado com sucesso.')
                ->with('importacao_veiculos_presentes_resumo', $resumo);
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
