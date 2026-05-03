<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Unidade;
use App\Models\Setores\Recebimento;

class RecebimentoItem extends Model
{
    protected $table = '_tb_recebimento_itens';

    protected $fillable = [
        'recebimento_id', 'sku', 'descricao', 'quantidade', 'status',
        'usuario_id', 'unidade_id'
    ];

    public function recebimento()
    {
        return $this->belongsTo(Recebimento::class, 'recebimento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }
}
