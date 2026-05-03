<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
    public static function registrar($acao, $dados)
    {
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => Auth::id(),
            'unidade_id' => Auth::user()->unidade_id ?? null,
            'acao' => $acao,
            'dados' => $dados,
            'ip_address' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
            'created_at' => now(),
        ]);
    }
}
