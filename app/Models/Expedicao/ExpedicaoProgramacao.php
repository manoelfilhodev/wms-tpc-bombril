<?php

namespace App\Models\Expedicao;

use Illuminate\Database\Eloquent\Model;

class ExpedicaoProgramacao extends Model
{
    protected $table = '_tb_expedicao_programacoes';

    protected $fillable = [
        'fo',
        'dt_sap',
        'agenda_entrega_em',
        'cidade_destino',
        'uf_destino',
        'cliente',
        'transportadora',
        'tipo_veiculo',
        'tipo_carga',
        'possui_picking',
        'status_previsao',
        'observacoes',
    ];

    protected $casts = [
        'agenda_entrega_em' => 'datetime',
        'possui_picking' => 'boolean',
    ];

    public function previsoes()
    {
        return $this->hasMany(ExpedicaoPrevisao::class, 'programacao_id');
    }

    public function ultimaPrevisao()
    {
        return $this->hasOne(ExpedicaoPrevisao::class, 'programacao_id')->latestOfMany();
    }
}