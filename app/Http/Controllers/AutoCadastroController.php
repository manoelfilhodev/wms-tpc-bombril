<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAutoCadastroRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AutoCadastroController extends Controller
{
    public function form(Request $request)
    {
        $token = $request->get('token');
    
        $invite = DB::table('user_invites')
            ->where('token', $token)
            ->where('valido_ate', '>=', now())
            ->where('usado', false)
            ->first();
    
        if (!$invite) {
            abort(403, 'Link inválido ou expirado.');
        }
    
        return view('cadastro.form', ['token' => $token, 'invite' => $invite]);
    }

    public function salvar(StoreAutoCadastroRequest $request)
    {
        $validated = $request->validated();

        $invite = DB::table('user_invites')
            ->where('token', $validated['token'])
            ->where('valido_ate', '>=', now())
            ->where('usado', false)
            ->first();
    
        if (!$invite) {
            abort(403, 'Token inválido ou expirado.');
        }
    
        $usuarioId = DB::table('_tb_usuarios')->insertGetId([
            'nome' => strtoupper($validated['nome']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['senha']),
            'unidade_id' => $invite->unidade_padrao,
            'tipo' => $invite->nivel_padrao,
            'status' => 'ativo',
            'nivel' => strtolower($invite->nivel_padrao),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $nomeUnidade = DB::table('_tb_unidades')
            ->where('id', $invite->unidade_padrao)
            ->value('nome');
        
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => $usuarioId,
            'unidade_id' => $invite->unidade_padrao,
            'acao' => 'Auto Cadastro',
            'dados' => '[NOVO USUARIO] - ' . strtoupper($validated['nome']) .
                       ' realizou auto cadastro com e-mail ' . strtolower($validated['email']) .
                       ', para a unidade "' . $nomeUnidade . '" (ID: ' . $invite->unidade_padrao . ')' .
                       ', nível de acesso: ' . $invite->nivel_padrao . '.',
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);

    
        DB::table('user_invites')->where('id', $invite->id)->update(['usado' => true]);
    
        return redirect()->route('cadastro.sucesso');

    }
    
    
}
