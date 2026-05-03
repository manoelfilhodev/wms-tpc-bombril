<?php

// RelatorioController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Unidade;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SeparacoesExport;
use App\Exports\ArmazenagemExport;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function separacoes(Request $request)
    {
        $query = DB::table('_tb_separacoes')
            ->join('_tb_usuarios', '_tb_separacoes.usuario_id', '=', '_tb_usuarios.id_user')
            ->join('_tb_unidades', '_tb_separacoes.unidade_id', '=', '_tb_unidades.id')
            ->select(
                '_tb_separacoes.*',
                '_tb_usuarios.nome as usuario_nome',
                '_tb_unidades.nome as unidade_nome'
            );

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('_tb_separacoes.data_separacao', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->filled('unidade_id')) {
            $query->where('_tb_separacoes.unidade_id', $request->unidade_id);
        }

        if ($request->filled('usuario_id')) {
            $query->where('_tb_separacoes.usuario_id', $request->usuario_id);
        }

        $separacoes = $query->orderBy('_tb_separacoes.data_separacao', 'desc')->paginate(20);

        // Dados para gráfico: total por usuário
        $grafico = DB::table('_tb_separacoes')
            ->join('_tb_usuarios', '_tb_separacoes.usuario_id', '=', '_tb_usuarios.id_user')
            ->select(DB::raw('COUNT(*) as total'), '_tb_usuarios.nome')
            ->groupBy('_tb_usuarios.nome')
            ->orderBy('total', 'desc')
            ->get();

        $usuarios = DB::table('_tb_usuarios')->orderBy('nome')->get();
        $unidades = DB::table('_tb_unidades')->orderBy('nome')->get();

        return view('relatorios.separacoes', compact('separacoes', 'usuarios', 'unidades', 'grafico'));
    }

    public function exportSeparacoesExcel(Request $request)
    {
        $dados = $this->filtrarSeparacoes($request)->get();
        $filename = 'separacoes_' . now()->format('Y-m-d_H-i') . '.xlsx';
        return Excel::download(new SeparacoesExport($dados), $filename);
    }

    public function exportSeparacoesPDF(Request $request)
    {
        $dados = $this->filtrarSeparacoes($request)->get();
        $filename = 'separacoes_' . now()->format('Y-m-d_H-i') . '.pdf';
        $pdf = Pdf::loadView('relatorios.separacoes_pdf', compact('dados'));
        return $pdf->download($filename);
    }

    private function filtrarSeparacoes(Request $request)
    {
        $query = DB::table('_tb_separacoes')
            ->join('_tb_usuarios', '_tb_separacoes.usuario_id', '=', '_tb_usuarios.id_user')
            ->join('_tb_unidades', '_tb_separacoes.unidade_id', '=', '_tb_unidades.id')
            ->select(
                '_tb_separacoes.*',
                '_tb_usuarios.nome as usuario_nome',
                '_tb_unidades.nome as unidade_nome'
            );

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('_tb_separacoes.data_separacao', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->filled('unidade_id')) {
            $query->where('_tb_separacoes.unidade_id', $request->unidade_id);
        }

        if ($request->filled('usuario_id')) {
            $query->where('_tb_separacoes.usuario_id', $request->usuario_id);
        }

        return $query->orderBy('_tb_separacoes.data_separacao', 'desc');
    }
    
    
    public function armazenagem(Request $request)
    {
        $query = DB::table('_tb_armazenagem')
            ->join('_tb_usuarios', '_tb_armazenagem.usuario_id', '=', '_tb_usuarios.id_user')
            ->join('_tb_unidades', '_tb_armazenagem.unidade_id', '=', '_tb_unidades.id')
            ->select(
                '_tb_armazenagem.*',
                '_tb_usuarios.nome as usuario_nome',
                '_tb_unidades.nome as unidade_nome'
            );

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('_tb_armazenagem.data', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->filled('unidade_id')) {
            $query->where('_tb_armazenagem.unidade_id', $request->unidade_id);
        }

        if ($request->filled('usuario_id')) {
            $query->where('_tb_armazenagem.usuario_id', $request->usuario_id);
        }

        $armazenagem = $query->orderBy('_tb_armazenagem.data_armazenagem', 'desc')->paginate(20);

        $grafico = DB::table('_tb_armazenagem')
            ->join('_tb_usuarios', '_tb_armazenagem.usuario_id', '=', '_tb_usuarios.id_user')
            ->select(DB::raw('COUNT(*) as total'), '_tb_usuarios.nome')
            ->groupBy('_tb_usuarios.nome')
            ->orderBy('total', 'desc')
            ->get();

        $usuarios = DB::table('_tb_usuarios')->orderBy('nome')->get();
        $unidades = DB::table('_tb_unidades')->orderBy('nome')->get();

        return view('relatorios.armazenagem', compact('armazenagem', 'usuarios', 'unidades', 'grafico'));
    }
    
    
    public function exportArmazenagemExcel(Request $request)
    {
        $dados = $this->filtrarArmazenagem($request)->get();
        $filename = 'armazenagem_' . now()->format('Y-m-d_H-i') . '.xlsx';
        return Excel::download(new ArmazenagemExport($dados), $filename);
    }

    public function exportArmazenagemPDF(Request $request)
    {
        $dados = $this->filtrarArmazenagem($request)->get();
        $filename = 'armazenagem_' . now()->format('Y-m-d_H-i') . '.pdf';
        $pdf = Pdf::loadView('relatorios.armazenagem_pdf', compact('dados'));
        return $pdf->download($filename);
    }

    private function filtrarArmazenagem(Request $request)
    {
        $query = DB::table('_tb_armazenagem')
            ->join('_tb_usuarios', '_tb_armazenagem.usuario_id', '=', '_tb_usuarios.id_user')
            ->join('_tb_unidades', '_tb_armazenagem.unidade_id', '=', '_tb_unidades.id')
            ->select(
                '_tb_armazenagem.*',
                '_tb_usuarios.nome as usuario_nome',
                '_tb_unidades.nome as unidade_nome'
            );

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('_tb_armazenagem.data_armazenagem', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->filled('unidade_id')) {
            $query->where('_tb_armazenagem.unidade_id', $request->unidade_id);
        }

        if ($request->filled('usuario_id')) {
            $query->where('_tb_armazenagem.usuario_id', $request->usuario_id);
        }

        return $query->orderBy('_tb_armazenagem.data_armazenagem', 'desc');
    }
}
