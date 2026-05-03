<?php

namespace App\Http\Controllers;

use App\Models\SugestaoAtualizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SugestoesController extends Controller
{
    public function index()
    {
        $sugestoes = SugestaoAtualizacao::with('usuario')->orderByDesc('created_at')->get();
        return view('sugestoes.index', compact('sugestoes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|max:255',
            'descricao' => 'required'
        ]);

        SugestaoAtualizacao::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'criado_por' => Auth::user()->id_user
        ]);

        return redirect()->back()->with('success', 'Sugestão enviada com sucesso!');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pendente,em_andamento,concluida,recusada',
        'resposta' => 'nullable|string|max:1000'
    ]);

    $sugestao = SugestaoAtualizacao::findOrFail($id);

    // atualiza a sugestão
    $sugestao->update([
        'status' => $request->status,
        'resposta' => $request->resposta
    ]);

    // salva no histórico
    \App\Models\RespostaSugestao::create([
        'sugestao_id' => $sugestao->id,
        'resposta' => $request->resposta,
        'status' => $request->status,
        'respondido_por' => Auth::user()->id_user
    ]);

    return redirect()->back()->with('success', 'Resposta registrada com sucesso!');
}

}
