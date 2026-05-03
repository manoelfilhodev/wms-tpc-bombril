<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Unidade;

class UserLog extends Model
{
    protected $table = '_tb_user_logs';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'unidade_id',
        'acao',
        'dados',
        'ip_address',
        'navegador',
        'created_at',
    ];

    // Relacionamento com usuário
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relacionamento com unidade
    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }
}
