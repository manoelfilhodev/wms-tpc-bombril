<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaDistribuicao extends Model
{
    use HasFactory;

    protected $table = '_tb_demanda_distribuicoes';

    protected $fillable = [
        'demanda_id',
        'separador_nome',
        'quantidade_pecas',
        'quantidade_skus',
        'finalizado_em',
        'resultado',
    ];

    protected $casts = [
        'finalizado_em' => 'datetime',
    ];
}
