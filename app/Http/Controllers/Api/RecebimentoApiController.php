<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setores\Recebimento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecebimentoApiController extends Controller
{
    public function listar()
    {
        return response()->json(
            Recebimento::select('id', 'nota_fiscal', 'fornecedor', 'placa', 'status','data_recebimento')
                ->where('status','pendente')
                ->orderByDesc('id')
                ->paginate(20)
        );
    }

    public function detalhes($id)
{
    $recebimento = DB::table('_tb_recebimento')
        ->select('id', 'nota_fiscal', 'fornecedor', 'placa', 'status', 'foto_inicio_veiculo')
        ->where('id', $id)
        ->first();

    return response()->json([
        'data' => $recebimento,
        'has_foto' => !empty($recebimento->foto_inicio_veiculo) // ✅ já retorna se tem ou não
    ]);
}


    public function uploadFotoInicio(Request $request, $id)
{
    $request->validate([
        'foto' => 'required|image|max:5120', // até 5MB
    ]);

    $nomeArquivo = 'foto_inicio_' . Str::uuid() . '.' . $request->file('foto')->extension();
    $request->file('foto')->storeAs('public/recebimento/fotos_inicio', $nomeArquivo);

    // Caminho relativo
    $caminhoRelativo = 'recebimento/fotos_inicio/' . $nomeArquivo;

    // Atualiza no banco
    DB::table('_tb_recebimento')
        ->where('id', $id)
        ->update(['foto_inicio_veiculo' => $caminhoRelativo]);

    return response()->json([
        'ok' => true,
        'path' => $caminhoRelativo
    ], 200);
}
    
public function listarItens($idRecebimento)
{
    try {
        $recebimento = Recebimento::with('itens')->findOrFail($idRecebimento);
        return response()->json($recebimento->itens);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Falha ao listar itens: ' . $e->getMessage()], 500);
    }
}

public function fecharConferencia(Request $request, $id)
{
    $request->validate([
        'foto_final' => 'required|image|max:5120',
        'assinatura' => 'required|image|max:2048',
    ]);

    $nomeFotoFim = 'foto_fim_' . Str::uuid() . '.' . $request->file('foto_final')->extension();
    $request->file('foto_final')->storeAs('public/recebimento/fotos_fim', $nomeFotoFim);

    $nomeAssinatura = 'assinatura_' . Str::uuid() . '.' . $request->file('assinatura')->extension();
    $request->file('assinatura')->storeAs('public/recebimento/assinaturas', $nomeAssinatura);

    // Atualiza no banco
    DB::table('_tb_recebimento')
        ->where('id', $id)
        ->update([
            'foto_fim_veiculo' => "recebimento/fotos_fim/$nomeFotoFim",
            'assinatura_conferente' => "recebimento/assinaturas/$nomeAssinatura",
            'status' => 'conferido'
        ]);

    return response()->json(['ok' => true], 200);
}

public function uploadFotoFim(Request $request, $id)
{
    $request->validate([
        'foto_final' => 'required|image|mimes:jpeg,png,jpg|max:5120',
    ]);

    // salva em storage/app/public/recebimento/fotos_fim
    $fileName = 'foto_final_' . Str::uuid() . '.' . $request->file('foto_final')->extension();
    $path = $request->file('foto_final')->storeAs('public/recebimento/fotos_fim', $fileName);

    // caminho relativo público: /storage/recebimento/fotos_fim/arquivo
    $publicUrl = Storage::url($path); // -> "/storage/recebimento/fotos_fim/..."

    DB::table('_tb_recebimento')
        ->where('id', $id)
        ->update(['foto_fim_veiculo' => ltrim($publicUrl, '/')]);

    return response()->json([
        'ok'   => true,
        'path' => $publicUrl,
    ]);
}


public function uploadAssinaturaFim(Request $request, $id)
{
    try {
        $recebimento = Recebimento::findOrFail($id);

        if (!$request->hasFile('assinatura_fim')) {
            return response()->json(['error' => 'A assinatura final é obrigatória.'], 422);
        }

        $file = $request->file('assinatura_fim');
        $filename = 'assinatura_fim_' . Str::uuid() . '.' . $file->extension();
        $path = $file->storeAs('public/recebimentos', $filename);

        // Atualiza no banco
        $recebimento->assinatura_conferente = str_replace('public/', 'storage/', $path);
      
        
        $recebimento->status = 'conferido';
        $recebimento->save();

        return response()->json([
            'message' => 'Assinatura final salva com sucesso!',
            'path' => asset($recebimento->assinatura_fim)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Falha ao salvar assinatura final',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function finalizarConferencia(Request $request, $id)
{
    $request->validate([
        'foto_final' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'assinatura_fim' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($request->hasFile('foto_final')) {
        $fotoPath = $request->file('foto_final')->store('fotos_finais', 'public');
    }

    if ($request->hasFile('assinatura_fim')) {
        $assinaturaPath = $request->file('assinatura_fim')->store('assinaturas_finais', 'public');
    }

    // salvar no banco, atualizar status, etc.
    return response()->json(['message' => 'Conferência finalizada com sucesso']);
}






}
