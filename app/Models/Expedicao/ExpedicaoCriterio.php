<?php

namespace App\Models\Expedicao;

use Illuminate\Database\Eloquent\Model;

class ExpedicaoCriterio extends Model
{
    protected $table = '_tb_expedicao_criterios';

    protected $fillable = [
        'categoria',
        'nome',
        'sku_min',
        'sku_max',
        'volume_min',
        'volume_max',
        'peso_min',
        'peso_max',
        'tipo_carga',
        'possui_picking',
        'tempo_previsto_minutos',
        'multiplicador',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'peso_min' => 'decimal:3',
        'peso_max' => 'decimal:3',
        'multiplicador' => 'decimal:2',
        'possui_picking' => 'boolean',
        'ativo' => 'boolean',
    ];
}