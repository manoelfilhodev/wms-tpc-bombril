<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Wms\WmsSkuPosicao;
use App\Services\SystemLogService;
use App\Services\Wms\ImportacaoWmsSkuPosicoesService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class WmsSkuPosicaoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [
            'sku' => trim((string) $request->input('sku')),
            'rua' => trim((string) $request->input('rua')),
            'endereco' => trim((string) $request->input('endereco')),
            'curva_abc' => trim((string) $request->input('curva_abc')),
        ];

        $vinculos = WmsSkuPosicao::query()
            ->with(['skuCadastro', 'posicao'])
            ->when($filtros['sku'] !== '', function ($query) use ($filtros): void {
                $query->where(function ($q) use ($filtros): void {
                    $q->where('_tb_wms_sku_posicoes.sku', 'like', "%{$filtros['sku']}%")
                        ->orWhereHas('skuCadastro', function ($skuQuery) use ($filtros): void {
                            $skuQuery->where('sku', 'like', "%{$filtros['sku']}%")
                                ->orWhere('descricao', 'like', "%{$filtros['sku']}%");
                        });
                });
            })
            ->when($filtros['rua'] !== '', function ($query) use ($filtros): void {
                $query->whereHas('posicao', fn ($q) => $q->where('rua', 'like', "%{$filtros['rua']}%"));
            })
            ->when($filtros['endereco'] !== '', function ($query) use ($filtros): void {
                $query->where(function ($q) use ($filtros): void {
                    $q->where('_tb_wms_sku_posicoes.endereco', 'like', "%{$filtros['endereco']}%")
                        ->orWhereHas('posicao', fn ($posicaoQuery) => $posicaoQuery->where('endereco', 'like', "%{$filtros['endereco']}%"));
                });
            })
            ->when($filtros['curva_abc'] !== '', function ($query) use ($filtros): void {
                $query->whereHas('skuCadastro', fn ($q) => $q->where('curva_abc', 'like', "%{$filtros['curva_abc']}%"));
            })
            ->join('_tb_wms_skus as skus', 'skus.id', '=', '_tb_wms_sku_posicoes.sku_id')
            ->join('_tb_wms_posicoes as posicoes', 'posicoes.id', '=', '_tb_wms_sku_posicoes.posicao_id')
            ->select('_tb_wms_sku_posicoes.*')
            ->orderBy('skus.sku')
            ->orderBy('posicoes.rua')
            ->orderBy('posicoes.posicao')
            ->paginate(25)
            ->withQueryString();

        return view('wms.sku-posicoes.index', compact('vinculos', 'filtros'));
    }

    public function importForm()
    {
        return view('wms.sku-posicoes.importar');
    }

    public function importar(Request $request, ImportacaoWmsSkuPosicoesService $service)
    {
        $this->validarArquivo($request);

        try {
            $resumo = $service->importar($request->file('arquivo'));

            SystemLogService::record([
                'module' => 'wms',
                'action' => 'importacao_wms_sku_posicoes_realizada',
                'description' => 'Usuário importou vínculos SKU x posição WMS.',
                'entity_type' => 'wms_sku_posicao',
                'new_values' => [
                    'arquivo' => $request->file('arquivo')?->getClientOriginalName(),
                    'resumo' => $resumo,
                ],
            ]);

            return back()
                ->with('success', 'Importação de vínculos SKU x posição concluída.')
                ->with('wms_sku_posicoes_resumo', $resumo);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function validarArquivo(Request $request): void
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
            'arquivo.required' => 'Selecione a base de posições com SKU.',
            'arquivo.file' => 'O upload enviado não é um arquivo válido.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);
    }
}
