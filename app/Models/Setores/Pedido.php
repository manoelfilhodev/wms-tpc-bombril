<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = '_tb_pedidos';

    protected $fillable = [
        'numero_pedido',
        'unidade_id',
        'status',
        'criado_por'
    ];

    public $timestamps = false;

    public function separacoes()
    {
        return $this->hasMany(Separacao::class, 'pedido_id');
    }

    public function itens()
    {
        return $this->hasMany(SeparacaoItem::class, 'pedido_id');
    }
    
    public function itensSeparacao()
    {
        return $this->hasMany(\App\Models\Setores\SeparacaoItem::class, 'pedido_id');
    }

}
