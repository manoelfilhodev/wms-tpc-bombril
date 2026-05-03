<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespostaSugestao extends Model
{
    protected $table = '_tb_respostas_sugestoes';

    protected $fillable = [
        'sugestao_id', 'resposta', 'status', 'respondido_por'
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'respondido_por', 'id_user');
    }
}
