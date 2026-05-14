<?php

namespace App\Models\Expedicao;

use Illuminate\Database\Eloquent\Model;

class ExpedicaoRota extends Model
{
    protected $table = '_tb_expedicao_rotas';

    protected $fillable = [
        'cidade_origem',
        'uf_origem',
        'cidade_destino',
        'uf_destino',
        'distancia_km',
        'tempo_api_minutos',
        'tempo_operacional_minutos',
        'ultima_consulta_em',
        'ativo',
    ];

    protected $casts = [
        'distancia_km' => 'decimal:2',
        'ultima_consulta_em' => 'datetime',
        'ativo' => 'boolean',
    ];
}