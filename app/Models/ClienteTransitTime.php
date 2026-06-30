<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteTransitTime extends Model
{
    protected $table = '_tb_wms_clientes_transit_time';

    protected $fillable = [
        'codigo_cliente',
        'nome_cliente',
        'zona_partida',
        'regiao',
        'uf',
        'cidade',
        'zona_transporte',
        'ciclo_inte',
        'transit_time_fechada_dias',
        'transit_time_fracionada_dias',
        'ativo',
    ];

    protected $casts = [
        'ciclo_inte' => 'integer',
        'transit_time_fechada_dias' => 'integer',
        'transit_time_fracionada_dias' => 'integer',
        'ativo' => 'boolean',
    ];
}
