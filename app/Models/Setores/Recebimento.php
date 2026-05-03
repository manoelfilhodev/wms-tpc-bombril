<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Unidade;
use App\Models\Setores\RecebimentoItem;

class Recebimento extends Model
{
    protected $table = '_tb_recebimento';

    protected $fillable = [
        'nota_fiscal',
        'fornecedor',
        'data_recebimento',
        'usuario_id',
        'unidade_id',
        'status'
    ];

    public function itens()
    {
        return $this->hasMany(RecebimentoItem::class, 'recebimento_id');
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
