<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EquipamentosExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class EquipamentoController extends Controller
{
    public function index(Request $request)
    {
        $equipamentos = Equipamento::query()
            ->when($request->tipo, function ($q) use ($request) {
                return $q->where('tipo', $request->tipo);
            })
            ->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->localizacao, function ($q) use ($request) {
                return $q->where('localizacao', $request->localizacao);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20); // ESSENCIAL para funcionar o links()
            
        $resumo = Equipamento::select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->get()
            ->pluck('total', 'tipo');   

        return view('equipamentos.index', compact('equipamentos', 'resumo'));
    }

    public function create()
    {
        return view('equipamentos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required',
            'modelo' => 'required',
        ]);
    
        $equipamento = Equipamento::create($request->all());
    
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id,
            'acao' => 'Cadastro de Equipamento',
            'dados' => '[EQUIPAMENTO] - ' . Auth::user()->nome .
                       ' cadastrou "' . $equipamento->tipo . ' - ' . $equipamento->modelo . '" ' .
                       'com número de série: ' . $equipamento->numero_serie,
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);
    
        return redirect()->route('equipamentos.index')->with('success', 'Equipamento cadastrado com sucesso!');
    }


    public function edit(Equipamento $equipamento)
    {
        return view('equipamentos.edit', compact('equipamento'));
    }

    public function update(Request $request, Equipamento $equipamento)
    {
        $equipamento->update($request->all());
    
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id,
            'acao' => 'Edição de Equipamento',
            'dados' => '[EQUIPAMENTO] - ' . Auth::user()->nome .
                       ' editou "' . $equipamento->tipo . ' - ' . $equipamento->modelo . '" ' .
                       'com ID: ' . $equipamento->id,
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);
    
        return redirect()->route('equipamentos.index')->with('success', 'Equipamento atualizado com sucesso!');
    }


    public function exportExcel()
    {
        return Excel::download(new EquipamentosExport, 'equipamentos.xlsx');
    }

    public function exportPDF()
    {
        $equipamentos = Equipamento::all();
        $pdf = PDF::loadView('equipamentos.report_pdf', compact('equipamentos'));
        return $pdf->download('equipamentos.pdf');
    }
}
