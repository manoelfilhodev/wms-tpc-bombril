<?php

namespace App\Http\Controllers\Setores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class ExecutarSeparacaoController extends Controller
{
    public function separarItem($id)
    {
        $item = DB::table('_tb_separacao_itens')->where('id', $id)->first();

        if (!$item) {
            return back()->with('error', 'Item da separação não encontrado.');
        }

        // Buscar o ID do SKU
        $sku_id = DB::table('_tb_materiais')
            ->where('sku', $item->sku)
            ->value('id');

        if (!$sku_id) {
            return back()->with('error', "SKU {$item->sku} não encontrado na base.");
        }

        // Buscar a posição com saldo suficiente e mais próxima (ordenada pelo código_posicao)
        $posicao = DB::table('_tb_saldo_estoque as s')
            ->join('_tb_posicoes as p', 'p.id', '=', 's.posicao_id')
            ->where('s.sku_id', $sku_id)
            ->where('s.quantidade', '>=', $item->quantidade)
            ->where('s.unidade_id', Auth::user()->unidade_id)
            ->orderBy('p.codigo_posicao', 'asc')
            ->select(
                'p.codigo_posicao',
                'p.id as posicao_id',
                's.quantidade as saldo_disponivel'
            )
            ->first();

        return view('setores.separacao.pedidos.separar_item', compact('item', 'posicao'));

        $itens = DB::table('_tb_separacao_itens')
            ->where('unidade_id', Auth::user()->unidade_id)
            ->orderBy('pedido_id')
            ->get();
    }



    public function executar(Request $request, $id)
    {
        $item = DB::table('_tb_separacao_itens')->where('id', $id)->first();

        if (!$item) {
            return back()->with('error', 'Item não encontrado.');
        }

        $request->validate([
            'quantidade_separada' => 'required|integer|min:1|max:' . $item->quantidade,
            'observacoes' => 'nullable|string|max:1000',
        ]);

        // Buscar o ID do SKU
        $sku_id = DB::table('_tb_materiais')
            ->where('sku', $item->sku)
            ->value('id');

        if (!$sku_id) {
            return back()->with('error', "SKU {$item->sku} não encontrado.");
        }

        // Buscar posição com saldo disponível
        $posicoes = DB::table('_tb_saldo_estoque')
            ->join('_tb_posicoes', '_tb_posicoes.id', '=', '_tb_saldo_estoque.posicao_id')
            ->where('_tb_saldo_estoque.sku_id', $sku_id)
            ->where('_tb_saldo_estoque.quantidade', '>=', $item->quantidade)
            ->where('_tb_saldo_estoque.unidade_id', Auth::user()->unidade_id)
            ->select(
                '_tb_posicoes.codigo_posicao',
                '_tb_posicoes.id as posicao_id',
                '_tb_saldo_estoque.quantidade as saldo_disponivel'
            )
            ->orderBy('_tb_posicoes.codigo_posicao', 'asc')
            ->get();

        $posicao = null;

        foreach ($posicoes as $pos) {
            $ocupacao = DB::table('_tb_posicoes_ocupadas')
                ->where('posicao_id', $pos->posicao_id)
                ->first();

            $expirada = !$ocupacao || $ocupacao->expiracao < now();
            $ocupada_por_mim = $ocupacao && $ocupacao->usuario_id == Auth::id();

            if ($expirada || $ocupada_por_mim) {
                // Se já estava ocupado por mim ou está livre, ocupo a posição
                DB::table('_tb_posicoes_ocupadas')
                    ->updateOrInsert(
                        ['posicao_id' => $pos->posicao_id],
                        [
                            'usuario_id' => Auth::id(),
                            'ocupada_em' => now(),
                            'expiracao' => now()->addMinutes(5)
                        ]
                    );

                $posicao = $pos;
                break;
            }
        }


        if (!$posicao) {
            return back()->with('error', 'Nenhuma posição com saldo disponível ou todas estão sendo utilizadas por outros operadores.');
        }


        if (!$posicao) {
            return back()->with('error', 'Nenhuma posição com saldo suficiente encontrada.');
        }

        // Atualizar o saldo da posição
        DB::table('_tb_saldo_estoque')
            ->where('sku_id', $sku_id)
            ->where('unidade_id', Auth::user()->unidade_id)
            ->where('posicao_id', $posicao->posicao_id)
            ->decrement('quantidade', $request->quantidade_separada);

        DB::table('_tb_movimentacoes_estoque')->insert([
            'sku_id' => $sku_id,
            'posicao_id' => $posicao->posicao_id,
            'unidade_id' => Auth::user()->unidade_id,
            'tipo' => 'SAIDA',
            'quantidade' => $request->quantidade_separada,
            'origem' => 'SEPARACAO',
            'referencia_id' => $id, // ID da linha da separação
            'usuario_id' => Auth::id(),
            'observacoes' => $request->observacoes,
            'created_at' => now()
        ]);

        // Inserir na doca virtual
        DB::table('_tb_doca_saida')->insert([
            'sku_id' => $sku_id,
            'quantidade' => $request->quantidade_separada,
            'posicao' => $posicao->codigo_posicao ?? 'DESCONHECIDO',
            'unidade_id' => Auth::user()->unidade_id,
            'usuario_id' => Auth::id(),
            'pedido_id' => $item->pedido_id,
            'created_at' => now()
        ]);

        // Atualizar item como conferido e salvar observações
        DB::table('_tb_separacao_itens')->where('id', $id)->update([
            'conferido' => 1,
            'coletado_por' => Auth::id(),
            'quantidade_separada' => $request->quantidade_separada,
            'observacoes' => $request->observacoes,
            'data_conferencia' => now(),
            'status' => 'FINALIZADA',
            'data_separacao' => now(),
        ]);

        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? 1,
            'acao' => 'Movimentação de Separação',
            'dados' => '[SEPARACAO] - ' . Auth::user()->nome .
                ' separou o SKU ' . $item->sku .
                ', quantidade ' . $request->quantidade_separada .
                ', na posição ' . ($posicao->codigo_posicao ?? 'DESCONHECIDO') .
                ', no pedido ' . $item->pedido_id . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now()
        ]);


        // Buscar próximo item não conferido do mesmo pedido
        $proximo = DB::table('_tb_separacao_itens')
            ->where('pedido_id', $item->pedido_id)
            ->where('conferido', 0)
            ->orderBy('id')
            ->first();

        if ($proximo) {
            return redirect()->route('separacoes.separar_item', $proximo->id)
                ->with('success', 'Separação realizada com sucesso!');
        } else {
            // Verifica se todos os itens do pedido estão com status FINALIZADA
            $itensRestantes = DB::table('_tb_separacao_itens')
                ->where('pedido_id', $item->pedido_id)
                ->where('status', '!=', 'FINALIZADA')
                ->count();

            if ($itensRestantes === 0) {
                return redirect()->route('separacoes.andamento')
                    ->with('success', 'Separação Finalizada!');
            }

            return redirect()->route('separacoes.andamento')
                ->with('success', 'Separação concluída para este item.');
        }
    }
}
