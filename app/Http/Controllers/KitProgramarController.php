<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\KitMontagem;

class KitProgramarController extends Controller
{
    public function index()
    {
        $kits = KitMontagem::orderBy('created_at', 'desc')->get();
        return view('kits.index', compact('kits'));
    }

    public function create()
    {
        return view('kits.form');
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'quantidade_programada' => 'required|integer|min:1'
    ]);

    $kit = KitMontagem::create([
        'nome'        => $data['nome'],
        'descricao'   => $data['descricao'] ?? null,
        'status'      => 'pendente',
        'usuario_id'  => auth()->id(),
    ]);

    // 👉 Cria as etiquetas logo após programar
    (new KitEtiquetaController)->criarEtiquetasDoKit($kit->id, $data['quantidade_programada']);

    return redirect()
        ->route('kit.confirmar', $kit->id)
        ->with('success', 'Programação registrada e etiquetas geradas no banco.');

    }

    public function confirmar($id)
    {
        $kit = KitMontagem::findOrFail($id);
        return view('kits.confirmar', compact('kit'));
    }
}
