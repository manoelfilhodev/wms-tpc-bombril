<?php
 
namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Controllers\Setores\ConferenciaController;


class ConferenciaController extends Controller
{
    
    public function formRessalva($id)
{
    $recebimento = DB::table('_tb_recebimento')->where('id', $id)->first();

    if (!$recebimento || $recebimento->status !== 'conferido') {
        return redirect()->route('setores.recebimento.painel')->with('error', 'Recebimento inválido ou ainda não conferido.');
    }

    return view('setores.conferencia.ressalva', compact('recebimento'));
} 
    
    public function salvarRessalva(Request $request, $id)
{
    $request->validate([
        'ressalva_assistente' => 'required|string|max:1000',
    ]);

    DB::table('_tb_recebimento')->where('id', $id)->update([
        'ressalva_assistente' => $request->ressalva_assistente,
        'updated_at' => now()
    ]);

    DB::table('_tb_user_logs')->insert([
        'usuario_id' => Auth::id(),
        'unidade_id' => Auth::user()->unidade_id ?? 1,
        'acao' => 'Ressalva pós-conferência',
        'dados' => '[RESSALVA] - ' . Auth::user()->nome . ' adicionou uma ressalva pós-conferência na NF ID: ' . $id,
        'ip_address' => request()->ip(),
        'navegador' => request()->header('User-Agent'),
        'created_at' => now()
    ]);

    return redirect()->route('setores.recebimento.painel')->with('success', 'Ressalva salva com sucesso.');
}




    
   public function reabrir($id)
{
    $recebimento = DB::table('_tb_recebimento')->where('id', $id)->first();

    if (!$recebimento || $recebimento->status !== 'conferido') {
        return back()->with('error', 'Recebimento não encontrado ou não está finalizado.');
    }

    // Reabre o status do recebimento
    DB::table('_tb_recebimento')->where('id', $id)->update([
        'status' => 'pendente',
        'assinatura_conferente' => null,
        'confirmado_por' => null,
        'data_fechamento' => null,
        'foto_fim_veiculo' => null
    ]);

    // Reseta os itens
    DB::table('_tb_recebimento_itens')
        ->where('recebimento_id', $id)
        ->update([
            'status' => 'pendente',
            'qtd_conferida' => null,
            'divergente' => 0,
            'avariado' => 0,
            'observacao' => null,
            'foto_avaria' => null,
            'usuario_id' => Auth::id(),
        ]);

    // Log de reabertura
    DB::table('_tb_user_logs')->insert([
        'usuario_id' => Auth::id(),
        'unidade_id' => Auth::user()->unidade_id ?? 1,
        'acao' => 'Reabertura de Conferência',
        'dados' => '[REABERTURA] - ' . Auth::user()->nome .
                   ' reabriu a conferência da NF ' . $recebimento->nota_fiscal . ' para ajustes.',
        'ip_address' => request()->ip(),
        'navegador' => request()->header('User-Agent'),
        'created_at' => now()
    ]);

    return redirect()->route('setores.recebimento.painel')->with('success', 'Conferência reaberta com sucesso.');
}

    
    public function salvarConferenciaItem(Request $request, $recebimento_id, $item_id)
{
    $item = DB::table('_tb_recebimento_itens')
        ->where('id', $item_id)
        ->where('recebimento_id', $recebimento_id)
        ->first();

    if (!$item) {
        return redirect()->back()->with('error', 'Item não encontrado.');
    }

    $dados = [
        'qtd_conferida' => $request->input('qtd_conferida'),
        'observacao' => $request->input('observacao'),
        'avariado' => $request->has('avariado') ? 1 : 0,
        'divergente' => $request->input('qtd_conferida') != $item->quantidade ? 1 : 0,
        'usuario_id' => auth()->user()->id ?? null,
        'status' => 'conferido',
    ];

    // Foto de avaria
    if ($request->hasFile('foto_avaria')) {
        $foto = $request->file('foto_avaria');
        $nomeFoto = 'avaria_rec_' . $recebimento_id . '_item_' . $item_id . '_' . time() . '.' . $foto->getClientOriginalExtension();
        $foto->storeAs('public/recebimentos/avarias', $nomeFoto);
        $dados['foto_avaria'] = 'recebimentos/avarias/' . $nomeFoto;
    }
    
    DB::table('_tb_recebimento_itens')->where('id', $item_id)->update($dados);
    
    DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Conferência de Item',
            'dados' => '[CONFERÊNCIA] - ' . Auth::user()->nome .
                       ' conferiu o SKU ' . $item->sku .
                       ', quantidade conferida: ' . $request->input('qtd_conferida') .
                       ', avaria: ' . ($request->has('avariado') ? 'Sim' : 'Não') .
                       ', observação: "' . ($request->input('observacao') ?? '-') . '"',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);

    return redirect()->route('setores.conferencia.itens', $recebimento_id)->with('success', 'Item conferido com sucesso.');
}

    
    public function formConferirItem($recebimento_id, $item_id)
{
    $item = DB::table('_tb_recebimento_itens')
        ->where('id', $item_id)
        ->where('recebimento_id', $recebimento_id)
        ->first();

    $recebimento = DB::table('_tb_recebimento')->where('id', $recebimento_id)->first();

    if (!$item || !$recebimento) {
        return redirect()->back()->with('error', 'Item ou recebimento não encontrado.');
    }

    return view('setores.conferencia.item', compact('item', 'recebimento'));
}

    
    public function formItemManual($id)
{
    $item = DB::table('_tb_recebimento_itens')->where('id', $id)->first();
    $recebimento = DB::table('_tb_recebimento')->where('id', $item->recebimento_id)->first();

    if (!$item || !$recebimento) {
        return redirect()->back()->with('error', 'Item ou Recebimento não encontrado.');
    }

    return view('setores.conferencia.item', compact('item', 'recebimento'));
}

    public function enviarItemManual(Request $request, $id)
    {
    
            $request->validate([
            'qtd_conferida' => 'required|integer|min:0',
            'observacao' => 'nullable|string|max:255',
            'foto_avaria' => 'nullable|image|max:10240',
        ]);
    
        $item = DB::table('_tb_recebimento_itens')->where('id', $id)->first();
        
    
        if (!$item) {
            return response()->json(['status' => 'erro', 'mensagem' => 'Item não encontrado.'], 404);
        }
    
        // Define se há divergência
        $divergente = $request->qtd_conferida != $item->quantidade;
    
        $dados = [
            'qtd_conferida' => $request->qtd_conferida,
            'observacao' => $request->observacao,
            'avariado' => $request->has('avariado') ? 1 : 0,
            'divergente' => $divergente ? 1 : 0,
            'status' => 'conferido',
        ];
    
        // Salvar imagem, se houver
        if ($request->hasFile('foto_avaria')) {
            $path = $request->file('foto_avaria')->store('recebimentos/avarias', 'public');
            $dados['foto_avaria'] = $path;
        }
    
        DB::table('_tb_recebimento_itens')->where('id', $id)->update($dados);
        
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Conferência de Item',
            'dados' => '[CONFERÊNCIA] - ' . Auth::user()->nome .
                       ' conferiu o SKU ' . ($item->sku ?? 'DESCONHECIDO') .
                       ', quantidade conferida: ' . $request->qtd_conferida .
                       ', avaria: ' . ($request->has('avariado') ? 'Sim' : 'Não') .
                       ', observação: "' . ($request->observacao ?? '-') . '"',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);
    
        return redirect()->route('setores.conferencia.itens', $item->recebimento_id)
    ->with('success', 'Item conferido com sucesso.');
        
    }
    
    public function contar(Request $request, $id)
    {
        // Validação básica
        $request->validate([
            'quantidade_conferida' => 'required|integer|min:0',
            'foto_avaria' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);
    
        try {
            $dados = [
                'qtd_conferida' => $request->quantidade_conferida,
                'observacao' => $request->observacao,
                'avariado' => $request->has('avariado') ? 1 : 0,
                'updated_at' => now(),
            ];
    
            if ($request->hasFile('foto_avaria')) {
                $file = $request->file('foto_avaria');
                $nome = 'avaria_rec_' . $id . '_item_' . $request->item_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $caminho = $file->storeAs('public/recebimentos/avarias', $nome);
                $dados['foto_avaria'] = 'recebimentos/avarias/' . $nome;
            }
    
            DB::table('_tb_recebimento_itens')
                ->where('id', $request->item_id)
                ->update($dados);
    
            return response()->json(['success' => true]);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

   
public function salvarFotoInicio(Request $request, $id)
{
    $request->validate([
        'foto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
    ]);

    $pasta = public_path('recebimento/fotos_inicio');
    if (!file_exists($pasta)) {
        mkdir($pasta, 0755, true);
    }

    $nomeArquivo = uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
    $request->file('foto')->move($pasta, $nomeArquivo);

    $relativo = "recebimento/fotos_inicio/{$nomeArquivo}";

    DB::table('_tb_recebimento')
        ->where('id', $id)
        ->update(['foto_inicio_veiculo' => $relativo, 'updated_at' => now()]);

    // Redireciona para os itens (ou volte para a mesma página com success)
    return redirect()
        ->route('setores.conferencia.itens', $id)
        ->with('success', 'Foto inicial salva com sucesso.');
}



    
    public function telaFotoInicio($id)
    {
        $recebimento = DB::table('_tb_recebimento')->where('id', $id)->first();
    
        if (!$recebimento) {
            return redirect()->back()->with('error', 'Recebimento não encontrado.');
        }
    
        // Se já existir a foto, pula direto para os itens
        if ($recebimento->foto_inicio_veiculo) {
            return redirect()->route('setores.conferencia.itens', $id);
        }
        
        if (!$recebimento->foto_inicio_veiculo) {
            DB::table('_tb_user_logs')->insert([
                'usuario_id' => Auth::id(),
                'unidade_id' => Auth::user()->unidade_id ?? 1,
                'acao' => 'Início de Conferência',
                'dados' => '[INÍCIO] - ' . Auth::user()->nome . ' iniciou a conferência da NF ' . $recebimento->nota_fiscal . '.',
                'ip_address' => request()->ip(),
                'navegador' => request()->header('User-Agent'),
                'created_at' => now()
            ]);
        }
    
        return view('setores.conferencia.foto_inicio', compact('recebimento'));
    }


    public function index()
    {
        $recebimentos = DB::table('_tb_recebimento')
            ->where('status', 'pendente')
            ->orderBy('data_recebimento', 'desc')
            ->get();

        return view('setores.conferencia.index', compact('recebimentos'));
    }

    public function itens($id)
    {
        $recebimento = DB::table('_tb_recebimento')->find($id);

        $itens = DB::table('_tb_recebimento_itens')
            ->where('recebimento_id', $id)
            ->get();

        return view('setores.conferencia.itens', compact('recebimento', 'itens'));
    }

    public function conferirItem(Request $request, $itemId)
    {
        $request->validate([
            'qtd_conferida' => 'required|integer|min:0',
        ]);
    
        $item = DB::table('_tb_recebimento_itens')->where('id', $itemId)->first();
    
        if (!$item) {
            return response()->json(['status' => 'erro', 'mensagem' => 'Item não encontrado.'], 404);
        }
    
        $divergente = $item->quantidade != $request->qtd_conferida;
        $avariado = $request->has('avariado') ? 1 : 0;
    
        $fotoPath = null;
        if ($request->hasFile('foto_avaria')) {
            $foto = $request->file('foto_avaria');
            $nome = 'avaria_rec_' . $item->recebimento_id . '_item_' . $itemId . '_' . time() . '.' . $foto->getClientOriginalExtension();
            $fotoPath = $foto->storeAs('recebimentos/avarias', $nome, 'public');
        }
    
        DB::table('_tb_recebimento_itens')->where('id', $itemId)->update([
            'qtd_conferida' => $request->qtd_conferida,
            'status' => 'conferido',
            'usuario_id' => Auth::id(),
            'divergente' => $divergente,
            'avariado' => $avariado,
            'foto_avaria' => $fotoPath,
            'observacao' => $request->observacao ?? null,
            'updated_at' => now(),
        ]);
    
        return response()->json([
            'status' => 'ok',
            'divergente' => $divergente,
            'mensagem' => $divergente
                ? 'Divergência detectada para este item!'
                : 'Conferência salva com sucesso.'
        ]);
    }

    

    public function gerarRelatorioPDF($id)
    {
        $recebimento = DB::table('_tb_recebimento')->find($id);
    
        $itens = DB::table('_tb_recebimento_itens')
            ->where('recebimento_id', $id)
            ->get();
    
        $pdf = Pdf::loadView('setores.conferencia.relatorio_pdf', compact('recebimento', 'itens'));
        return $pdf->download('relatorio_conferencia_' . $recebimento->nota_fiscal . '.pdf');
    }

    
    public function finalizar(Request $request, $id)
    {
        $request->validate([
            'confirmacao' => 'required',
            'foto_fim_veiculo' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);
    
        $itens = DB::table('_tb_recebimento_itens')
            ->where('recebimento_id', $id)
            ->get();
    
        $temDivergencia = $itens->contains(function ($item) {
            return $item->qtd_conferida != $item->quantidade;
        });
    
        // Salvar a foto final do veículo
        $foto = $request->file('foto_fim_veiculo');
        $nome = 'fim_veiculo_rec_' . $id . '_' . time() . '.' . $foto->getClientOriginalExtension();
        $caminho = 'recebimentos/fotos_veiculo/' . $nome;
        $foto->storeAs('public/' . $caminho);
    
        // Atualiza status e salva foto no recebimento
        DB::table('_tb_recebimento')->where('id', $id)->update([
            'status' => 'conferido',
            'foto_fim_veiculo' => $caminho,
            'assinatura_conferente' => Auth::user()->nome,
            'confirmado_por' => Auth::id(),
            'data_fechamento' => now()
        ]);
    
        // Log
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Fechamento de Conferência',
            'dados' => '[CONFERÊNCIA] - ' . Auth::user()->nome .
                       ' finalizou a conferência da NF ' . ($itens->first()->nota_fiscal ?? '-') .
                       ', com ' . $itens->filter(fn($i) => $i->qtd_conferida != $i->quantidade)->count() . ' divergência(s).',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);
    
        return redirect()->route('setores.conferencia.index')->with('success', $temDivergencia
            ? 'Conferência finalizada com divergência registrada.'
            : 'Conferência finalizada com sucesso.');
    }
    
    public function listar()
    {
        $dados = DB::table('_tb_recebimento')
            ->select('id', 'nf', 'fornecedor', 'data', 'status', 'progresso')
            ->orderByDesc('data')
            ->get();
    
        return response()->json($dados);
    }




}
