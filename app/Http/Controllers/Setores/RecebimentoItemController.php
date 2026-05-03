<?php
namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use App\Models\Setores\RecebimentoItem;
use App\Models\Setores\Recebimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecebimentoItemController extends Controller
{
    public function index($recebimento_id)
    {
        $recebimento = Recebimento::findOrFail($recebimento_id);
        $itens = RecebimentoItem::where('recebimento_id', $recebimento_id)->get();

        return view('setores.recebimento.itens.index', compact('recebimento', 'itens'));
    }

    public function create($recebimento_id)
    {
        $recebimento = Recebimento::findOrFail($recebimento_id);
        return view('setores.recebimento.itens.create', compact('recebimento'));
    }

    public function store(Request $request, $recebimento_id)
    {
        $request->validate([
            'sku' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'status' => 'required|in:pendente,conferido,armazenado'
        ]);

        RecebimentoItem::create([
            'recebimento_id' => $recebimento_id,
            'sku' => $request->sku,
            'descricao' => $request->descricao,
            'quantidade' => $request->quantidade,
            'status' => $request->status,
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1
        ]);

        return redirect()->route('recebimento.itens.index', $recebimento_id)->with('success', 'Item adicionado com sucesso.');
    }
    
    public function imprimirTodas($id)
{
    $dados = DB::table('_tb_recebimento_sku')
        ->where('recebimento_id', $id)
        ->get();

    $pdf = \PDF::loadView('etiquetas.pdf', compact('dados'));
    return $pdf->stream("etiquetas.pdf");
}

}
