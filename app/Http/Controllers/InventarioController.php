<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InventarioController extends Controller
{
    public function uploadForm()
    {
        return view('inventario.upload_mb51');
    }

    public function importarMB51SalvarTemporario(Request $request)
    {
        try {
            $arquivo = $request->file('arquivo');

            if (!$arquivo || !$arquivo->isValid()) {
                return back()->with('error', 'Arquivo inválido ou corrompido.');
            }

            $spreadsheet = IOFactory::load($arquivo->getPathname());
            $dados = $spreadsheet->getActiveSheet()->toArray();

            if (count($dados) <= 1) {
                return back()->with('error', 'A planilha está vazia ou não possui cabeçalho.');
            }

            $importados = 0;

            foreach ($dados as $i => $linha) {
                if ($i === 0) continue;

                $sku = trim($linha[0] ?? '');
                $descricao = trim($linha[3] ?? '');
                $tipoMov = trim($linha[6] ?? '');
                $posicao = trim($linha[10] ?? '');

                if (!$sku || !$tipoMov) continue;

                DB::table('_tb_relatorio_mb51')->insert([
                    'sku' => $sku,
                    'descricao' => $descricao,
                    'tipo_movimento' => $tipoMov,
                    'posicao' => $posicao,
                    'data_importacao' => now()->toDateString(),
                    'created_at' => now()
                ]);

                $importados++;
            }

            return back()->with('success', "Importação MB51 salva com sucesso. Registros: $importados");

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar MB51 temporariamente', ['exception' => $e]);
            return back()->with('error', 'Erro ao importar MB51: ' . $e->getMessage());
        }
    }
}
