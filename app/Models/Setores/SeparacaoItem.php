<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;

class SeparacaoItem extends Model
{
    protected $table = '_tb_separacao_itens';

    protected $fillable = [
        'pedido_id',
        'separacao_id',
        'sku',
        'quantidade',
        'centro',
        'fo',
        'usuario_id',
        'unidade_id',
        'conferido',
        'data_conferencia'
    ];

    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function separacao()
    {
        return $this->belongsTo(Separacao::class, 'separacao_id');
    }
}
