<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtiquetaHydra;
use Carbon\Carbon;
use DB;
use FPDF;
use App\Helpers\LogHelper;

class EtiquetaController extends Controller
{
    public function imprimirSelecionadas(Request $request)
    {
        $ids = $request->input('ids', []);
        $modo = $request->input('modo_impressao', 'normal');

        $etiquetas = EtiquetaHydra::whereIn('id', $ids)->get();

        if ($etiquetas->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhuma etiqueta selecionada.');
        }

        $etiquetas_expandidas = [];

        foreach ($etiquetas as $etiqueta) {
            $quantidade_total = $etiqueta->quantidade ?? 1;
            $sku = $etiqueta->produto;

            if ($modo === 'multiplo') {
                $multiplo = DB::table('_tb_materiais_multipack')
                    ->where('sku', $sku)
                    ->value('fator_embalagem') ?? 1;

                $qtd_etiquetas = ceil($quantidade_total / $multiplo);

                for ($i = 0; $i < $qtd_etiquetas; $i++) {
                    $qtd = ($i == $qtd_etiquetas - 1)
                        ? ($quantidade_total - ($multiplo * $i))
                        : $multiplo;

                    $clone = clone $etiqueta;
                    $clone->quantidade = $qtd;
                    $etiquetas_expandidas[] = $clone;
                }
            } else {
                for ($i = 0; $i < $quantidade_total; $i++) {
                    $clone = clone $etiqueta;
                    $clone->quantidade = 1;
                    $etiquetas_expandidas[] = $clone;
                }
            }
        }

        return view('etiquetas.hydra.reimprimir', [
            'etiquetas' => $etiquetas_expandidas
        ]);
    }

    public function reimprimirSelecionar($fo)
    {
        $etiquetas = EtiquetaHydra::where('fo', $fo)->orderBy('produto')->get();
        return view('etiquetas.hydra.selecionar', compact('etiquetas', 'fo'));
    }

    public function reimprimir($fo)
    {
        $etiquetas = EtiquetaHydra::where('fo', $fo)->orderBy('produto')->get();

        if ($etiquetas->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhuma etiqueta encontrada para essa FO.');
        }

        return view('etiquetas.hydra.reimprimir', compact('etiquetas'));
    }

    public function historico(Request $request)
    {
        $query = \App\Models\EtiquetaHydra::query();

        if ($request->filled('fo')) {
            $query->where('fo', 'like', '%' . $request->fo . '%');
        }

        if ($request->filled('produto')) {
            $query->where('produto', 'like', '%' . $request->produto . '%');
        }

        if ($request->filled('cliente')) {
            $query->where('cliente', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('data')) {
            $query->whereDate('data_gerada', $request->data);
        }

        $etiquetas = $query->orderBy('data_gerada', 'desc')->paginate(30);

        return view('etiquetas.hydra.historico', compact('etiquetas'));
    }

    public function gerar(Request $request)
    {
        $modo = $request->input('modo_impressao', 'normal');

        $dados = DB::table('nome_da_sua_tabela') // substitua pelo nome correto
            ->select('FO', 'REMESSA', 'COD_CLIENTE', 'CLIENTE', 'PRODUTO', 'QTD', 'CIDADE')
            ->get();

        $pdf = new \FPDF('L', 'mm', [120, 60]);
        $pdf->SetAutoPageBreak(false);

        foreach ($dados as $row) {
            $uf = $this->obterUF($row->CIDADE);
            $multiplo = 1;

            if ($modo === 'multiplo') {
                $multiplo = DB::table('_tb_materiais_multipack')
                    ->where('sku', $row->PRODUTO)
                    ->value('fator_embalagem') ?? 1;
            }

            $qtd_etiquetas = ($modo === 'multiplo') ? ceil($row->QTD / $multiplo) : $row->QTD;

            for ($i = 0; $i < $qtd_etiquetas; $i++) {
                $quantidade = ($modo === 'multiplo')
                    ? ($i == $qtd_etiquetas - 1 ? $row->QTD - ($multiplo * $i) : $multiplo)
                    : 1;

                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(5, 5);
                $pdf->Cell(110, 6, $row->COD_CLIENTE, 0, 1, 'L');
                $pdf->SetXY(95, 5);
                $pdf->Cell(20, 6, 'DOCA 01', 0, 1, 'R');
                $pdf->SetXY(95, 12);
                $pdf->Cell(20, 6, $row->FO, 0, 1, 'R');

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Remessa:', 0, 0);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(80, 5, $row->REMESSA, 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Cliente:', 0, 0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(80, 5, substr($row->CLIENTE, 0, 50), 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Cidade:', 0, 0);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(80, 5, $row->CIDADE, 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'UF:', 0, 0);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(80, 5, $uf, 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Produto:', 0, 0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(80, 5, $row->PRODUTO, 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Qtde Pçs:', 0, 0);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(80, 5, $quantidade, 0, 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(30, 5, 'Data - hora:', 0, 0);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(80, 5, Carbon::now()->format('d/m/Y H:i:s'), 0, 1);
            }
        }
        
        // Captura as FOs únicas
        $fosGeradas = $dados->pluck('FO')->unique()->implode(', ');
        
        // Calcula a quantidade total de etiquetas geradas
        $totalEtiquetas = 0;
        foreach ($dados as $row) {
            $multiplo = ($modo === 'multiplo') ? (DB::table('_tb_materiais_multipack')->where('sku', $row->PRODUTO)->value('fator_embalagem') ?? 1) : 1;
            $qtdEtiquetas = ($modo === 'multiplo') ? ceil($row->QTD / $multiplo) : $row->QTD;
            $totalEtiquetas += $qtdEtiquetas;
        }
        
        // Registra o log
        LogHelper::registrar(
            'Geração de Etiquetas',
            "Arquivo: etiquetas.pdf | FOs: $fosGeradas | Total de etiquetas: $totalEtiquetas"
        );

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="etiquetas.pdf"');
    }

    public function viewHtml(Request $request)
    {
        $etiquetas = [];
        $modo = $request->input('modo_impressao', 'normal');

        if ($request->filled('dados')) {
            $linhas = explode("\n", trim($request->input('dados')));
            foreach ($linhas as $linha) {
                $colunas = explode("\t", $linha);
                if (count($colunas) >= 8) {
                    $qtd_total = (int) $colunas[5];
                    $sku = $colunas[4];
                    $fator = 1;

                    if ($modo === 'multiplo') {
                        $fator = DB::table('_tb_materiais_multipack')
                            ->where('sku', $sku)
                            ->value('fator_embalagem') ?? 1;
                    }

                    $qtd_etiquetas = ($modo === 'multiplo') ? ceil($qtd_total / $fator) : $qtd_total;

                    for ($i = 0; $i < $qtd_etiquetas; $i++) {
                        $quantidade = ($modo === 'multiplo')
                            ? ($i == $qtd_etiquetas - 1 ? $qtd_total - ($fator * $i) : $fator)
                            : 1;

                        $etiquetas[] = (object)[
                            'FO' => $colunas[0],
                            'REMESSA' => $colunas[1],
                            'RECEBEDOR' => $colunas[2],
                            'CLIENTE' => $colunas[3],
                            'PRODUTO' => $colunas[4],
                            'QTD' => $quantidade,
                            'CIDADE' => $colunas[6],
                            'UF' => strtoupper($colunas[7]),
                            'DOCA' => '01',
                        ];
                    }
                }
            }
        }

        $etiquetas = collect($etiquetas)->sortBy('PRODUTO')->values();
        
        // Registrar log somente se houve etiquetas geradas
        if ($etiquetas->count() > 0) {
            $fosGeradas = $etiquetas->pluck('FO')->unique()->implode(', ');
            $totalEtiquetas = $etiquetas->count();
        
            \App\Helpers\LogHelper::registrar(
                'Geração de Etiquetas (HTML)',
                "FOs: $fosGeradas | Total de etiquetas geradas: $totalEtiquetas"
            );
        }

        return view('etiquetas.index', compact('etiquetas'));
    }

    private function obterUF($cidade)
    {
        $cidade = strtoupper($cidade);
        if (str_contains($cidade, 'RIO') || str_contains($cidade, 'PETROPOLIS') || str_contains($cidade, 'NITEROI') || str_contains($cidade, 'TERESOPOLIS')) {
            return 'RJ';
        }
        return 'ES';
    }
}
