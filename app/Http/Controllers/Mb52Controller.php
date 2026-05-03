<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportMb52Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Mb52Controller extends Controller
{
    public function uploadForm()
    {
        return view('inventario.mb52.upload');
    }

    public function importar(ImportMb52Request $request)
{
    try {
        $arquivo = $request->file('arquivo');
        $spreadsheet = IOFactory::load($arquivo->getPathname());
        $dados = $spreadsheet->getActiveSheet()->toArray();

        $importados = 0;
        $hoje = now()->format('Y-m-d');

        foreach ($dados as $i => $linha) {
            if ($i === 0 || empty($linha[1])) continue;

            DB::table('_tb_relatorio_mb52')->insert([
                'data_referencia' => $hoje,
                'centro' => trim($linha[0] ?? ''),
                'material' => trim($linha[1] ?? ''),
                'descricao' => trim($linha[2] ?? ''),
                'deposito' => trim($linha[3] ?? ''),
                'unidade_medida' => trim($linha[4] ?? ''),
                'utilizacao_livre' => $this->parseValorBrasileiro($linha[5] ?? '0'),
                'bloqueado' => $this->parseValorBrasileiro($linha[6] ?? '0'),
                'controle_qualidade' => $this->parseValorBrasileiro($linha[7] ?? '0'),
                'transito_te' => $this->parseValorBrasileiro($linha[8] ?? '0'),
                'created_at' => now(),
            ]);

            $importados++;
        }

        return back()->with('success', "Importação MB52 salva com data $hoje. Registros inseridos: $importados.");

    } catch (\Exception $e) {
        \Log::error('Erro ao importar MB52', ['exception' => $e]);
        return back()->with('error', 'Erro ao importar MB52: ' . $e->getMessage());
    }
}

public function excluirHoje()
{
    $hoje = now()->format('Y-m-d');
    $usuario = Auth::user();

    $quantidade = DB::table('_tb_relatorio_mb52')
        ->where('data_referencia', $hoje)
        ->count();

    if ($quantidade > 0) {
        DB::table('_tb_relatorio_mb52')->where('data_referencia', $hoje)->delete();

        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => $usuario->unidade_id,
            'acao' => 'Exclusão de MB52',
            'dados' => '[MB52] - ' . $usuario->name .
                ' excluiu ' . $quantidade . ' registros da MB52 referentes à data ' . $hoje . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);

        return back()->with('success', "Registros da MB52 de hoje ($hoje) foram excluídos com sucesso.");
    }

    return back()->with('error', "Nenhum registro da MB52 encontrado para hoje ($hoje).");
}



    
   private function parseValorBrasileiro($valor)
{
    $valor = trim((string) $valor);

    // Se vier como já numérico (float/int), retorna direto
    if (is_numeric($valor)) {
        return floatval($valor);
    }

    // Trata valor com vírgula como decimal BR (ex: "15.800,000")
    if (str_contains($valor, ',')) {
        $valor = str_replace('.', '', $valor); // remove milhar
        $valor = str_replace(',', '.', $valor); // converte decimal
    }

    return is_numeric($valor) ? floatval($valor) : 0.0;
}


}
