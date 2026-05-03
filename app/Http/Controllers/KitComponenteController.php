<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitComponente;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;

class KitComponenteController extends Controller
{
    public function index()
    {
        $componentes = KitComponente::with(['kit', 'componente'])->paginate(10);
        return view('kit.componentes', compact('componentes'));
    }

    public function create()
    {
        $materiais = Material::orderBy('sku')->get();
        return view('kit.componentes_create', compact('materiais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kit_material_id' => 'required|exists:_tb_materiais,id',
            'componente_material_id' => 'required|exists:_tb_materiais,id|different:kit_material_id',
            'quantidade_por_kit' => 'required|numeric|min:0.001',
        ]);

        KitComponente::create([
            'kit_material_id' => $request->kit_material_id,
            'componente_material_id' => $request->componente_material_id,
            'quantidade_por_kit' => $request->quantidade_por_kit,
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id
        ]);

        return redirect()->route('kits.componentes.index')->with('success', 'Componente adicionado com sucesso!');
    }
}
