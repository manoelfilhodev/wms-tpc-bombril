<?php
// app/Http/Controllers/ContagemItemController.php

namespace App\Http\Controllers;

use App\Models\ContagemItem;
use App\Models\ItemContagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ContagemItensExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\LogHelper;

class ContagemItemController extends Controller
{
    public function index(Request $request)
    {
        $query = ContagemItem::with(['usuario', 'material']);

        if ($request->filled('codigo_material')) {
            $query->where('codigo_material', $request->codigo_material);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_contagem', [$request->data_inicio, $request->data_fim]);
        }

        $contagens = $query->orderByDesc('data_contagem')->paginate(10);

        return view('contagem.itens.index', compact('contagens'));
    }

    public function create()
    {
        $materiais = ItemContagem::orderBy('descricao')->get();
        return view('contagem.itens.create', compact('materiais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_material' => 'required|exists:_tb_itens_contagem,codigo_material',
            'quantidade' => 'required|integer|min:1',
        ]);

        ContagemItem::create([
            'codigo_material' => $request->codigo_material,
            'quantidade'      => $request->quantidade,
            'usuario_id'      => Auth::id(),
            'unidade_id'      => $request->unidade_id,
            'data_contagem'   => now(),
        ]);
        
        // Salvando no log
        $material = ItemContagem::where('codigo_material', $request->codigo_material)->first();
        LogHelper::registrar(
            'Contagem de Itens',
            '[CONTAGEM] - ' . Auth::user()->nome . ' contou ' . $request->quantidade . ' unidades do material: ' . $material->descricao
        );

        return redirect()->route('contagem.itens.index')->with('success', 'Contagem registrada com sucesso.');
    }

    public function exportExcel()
    {
        return Excel::download(new ContagemItensExport, 'contagem_itens.xlsx');
    }
    
    public function storeMultiple(Request $request)
{
    $request->validate([
        'quantidades' => 'required|array|min:6',
        'quantidades.*' => 'required|integer|min:0',
    ]);

    foreach ($request->quantidades as $codigo => $qtd) {
        ContagemItem::create([
            'codigo_material' => $codigo,
            'quantidade'      => $qtd,
            'usuario_id'      => Auth::id(),
            'unidade_id'      => $request->unidade_id,
            'data_contagem'   => now(),
        ]);
    }

    return redirect()->route('contagem.itens.index')
        ->with('success', 'Contagem dos 6 itens registrada com sucesso!');
}

}
