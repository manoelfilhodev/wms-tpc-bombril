<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;

class Separacao extends Model
{
    protected $table = '_tb_separacoes';

    protected $fillable = [
        'pedido',
        'sku',
        'quantidade',
        'endereco',
        'observacoes',
        'usuario_id',
        'unidade_id',
    ];

    public $timestamps = false;
    
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    
    public function itens()
    {
        return $this->hasMany(SeparacaoItem::class, 'separacao_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
    
    public function unidade()
    {
        return $this->belongsTo(\App\Models\Unidade::class, 'unidade_id');
    }


}