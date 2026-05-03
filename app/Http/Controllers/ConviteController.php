<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ConviteController extends Controller
{
    public function index()
    {
        $unidades = DB::table('_tb_unidades')
        ->where('status', 'ativo')
        ->orderBy('nome')
        ->get();

        return view('convites.index', compact('unidades'));
    }

    public function gerar(Request $request)
    {
        $unidade = $request->input('unidade');
$nivelPadrao = $request->input('nivel_padrao');
$validade = $request->input('validade');
        $token = Str::random(32);

        DB::table('user_invites')->insert([
            'token' => $token,
            'nivel_padrao' => $request->nivel_padrao,
            'unidade_padrao' => $request->unidade,
            'valido_ate' => now()->addHours((int) $request->validade),
            'created_at' => now(),
        ]);
        
        $unidade = DB::table('_tb_unidades')->where('id', $request->unidade)->value('nome');
        
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id,
            'acao' => 'Gerador de Link',
            'dados' => '[CONVITE] - ' . Auth::user()->nome .
           ' gerou link de cadastro para a unidade "' . $unidade . '" ' .
           '(ID: ' . $request->unidade . '), nível: ' . $request->nivel_padrao .
           ', validade: ' . $request->validade . 'h.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
]);



        $link = url('/cadastro?token=' . $token);

        return back()->with('link_gerado', $link);
    }
}
