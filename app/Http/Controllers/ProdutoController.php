<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('_tb_materiais')->orderBy('id', 'desc');

        if ($request->filled('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }

        if ($request->filled('descricao')) {
            $query->where('descricao', 'like', '%' . $request->descricao . '%');
        }

        if ($request->filled('ean')) {
            $query->where('ean', 'like', '%' . $request->ean . '%');
        }

        $produtos = $query->paginate(15)->appends($request->all());

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:50',
            'ean' => 'nullable|string|max:50',
            'descricao' => 'required|string|max:255',
            'quantidade_estoque' => 'required|integer|min:0',
            'lastro' => 'required|integer|min:0',
            'camada' => 'required|integer|min:0',
            'paletizacao' => 'required|integer|min:0',
        ]);

        $idProduto = DB::table('_tb_materiais')->insertGetId([
            'unidade_id' => auth()->user()->unidade_id,
            'sku' => $request->sku,
            'ean' => $request->ean,
            'descricao' => $request->descricao,
            'quantidade_estoque' => $request->quantidade_estoque,
            'lastro' => $request->lastro,
            'camada' => $request->camada,
            'paletizacao' => $request->paletizacao,
            'created_at' => now(),
        ]);

        $this->registrarLog('Criou produto', [
            'id' => $idProduto,
            'sku' => $request->sku,
            'descricao' => $request->descricao
        ]);

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $produto = DB::table('_tb_materiais')->find($id);
        return view('produtos.edit', compact('produto'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sku' => 'required|string|max:50',
            'ean' => 'nullable|string|max:50',
            'descricao' => 'required|string|max:255',
            'quantidade_estoque' => 'required|integer|min:0',
            'lastro' => 'required|integer|min:0',
            'camada' => 'required|integer|min:0',
            'paletizacao' => 'required|integer|min:0',
        ]);

        DB::table('_tb_materiais')->where('id', $id)->update([
            'sku' => $request->sku,
            'ean' => $request->ean,
            'descricao' => $request->descricao,
            'quantidade_estoque' => $request->quantidade_estoque,
            'lastro' => $request->lastro,
            'camada' => $request->camada,
            'paletizacao' => $request->paletizacao,
        ]);

        $this->registrarLog('Editou produto', [
            'id' => $id,
            'sku' => $request->sku,
            'descricao' => $request->descricao
        ]);

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $produto = DB::table('_tb_materiais')->find($id);

        DB::table('_tb_materiais')->where('id', $id)->delete();

        $this->registrarLog('Excluiu produto', [
            'id' => $id,
            'sku' => $produto->sku ?? null,
            'descricao' => $produto->descricao ?? null
        ]);

        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }

    private function registrarLog($acao, $dados)
    {
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => auth()->user()->id_user,
            'unidade_id' => auth()->user()->unidade_id,
            'acao' => $acao,
            'dados' => json_encode($dados),
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);
    }
}
