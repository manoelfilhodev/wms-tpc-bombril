<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    use HasFactory;

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
        'total_itens',
        'total_itens_com_sobra',
        'separador_id',
    ];
    
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
