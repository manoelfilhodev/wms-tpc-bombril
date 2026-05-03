<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RespostaSugestao;

class SugestaoAtualizacao extends Model
{
    protected $table = '_tb_sugestoes_atualizacoes';

    protected $fillable = [
        'titulo', 'descricao', 'status', 'resposta', 'criado_por'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'criado_por', 'id_user');
    }
    
    public function respostas()
    {
        return $this->hasMany(RespostaSugestao::class, 'sugestao_id');
    }
}
