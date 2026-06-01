<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    use HasFactory;

    public const DATA_OPERACIONAL_MINIMA = '2000-01-01 00:00:00';

    protected $table = '_tb_demanda';

    protected $fillable = [
        'fo',
        'stage',
        'cliente',
        'transportadora',
        'doca',
        'tipo',
        'quantidade',
        'peso',
        'valor_carga',
        'hora_agendada',
        'entrada',
        'saida',
        'status',
        'veiculo',
        'possui_sobra',
        'separacao_iniciada_em',
        'separacao_finalizada_em',
        'separacao_resultado',
        'conferencia_iniciada_em',
        'conferencia_finalizada_em',
        'carregamento_iniciado_em',
        'carregamento_finalizado_em',
        'saida_veiculo_em',
        'saida_veiculo_usuario_id',
        'saida_veiculo_observacao',
        'total_itens',
        'total_itens_com_sobra',
        'separador_id',
    ];

    protected $casts = [
        'possui_sobra' => 'boolean',
        'separacao_iniciada_em' => 'datetime',
        'separacao_finalizada_em' => 'datetime',
        'conferencia_iniciada_em' => 'datetime',
        'conferencia_finalizada_em' => 'datetime',
        'carregamento_iniciado_em' => 'datetime',
        'carregamento_finalizado_em' => 'datetime',
        'saida_veiculo_em' => 'datetime',
    ];

    public function getSeparacaoIniciadaValidaAttribute()
    {
        if (! $this->separacao_iniciada_em) {
            return null;
        }

        return $this->separacao_iniciada_em->gte(static::DATA_OPERACIONAL_MINIMA)
            ? $this->separacao_iniciada_em
            : null;
    }
    
public function history()
{
    return $this->hasMany(\App\Models\DemandaHistory::class, 'demanda_id');
}

public function itens()
{
    return $this->hasMany(\App\Models\DemandaItem::class, 'demanda_id');
}

public function distribuicoes()
{
    return $this->hasMany(\App\Models\DemandaDistribuicao::class, 'demanda_id');
}

public function separador()
{
    return $this->belongsTo(\App\Models\User::class, 'separador_id', 'id_user');
}

public function separadores()
{
    return $this->belongsToMany(
        \App\Models\User::class,
        '_tb_demanda_separadores',
        'demanda_id',
        'usuario_id',
        'id',
        'id_user'
    )->withTimestamps();
}
}
