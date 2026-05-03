<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispositivoAutorizado extends Model
{
    protected $table = '_tb_dispositivos_autorizados';

    protected $fillable = [
        'usuario_id',
        'nome_dispositivo',
        'device_id',
        'tipo',
        'perfil_permitido',
        'ativo',
        'ultimo_acesso_em',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ultimo_acesso_em' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_user');
    }
}
