<?php

namespace App\Models\Expedicao;

use Illuminate\Database\Eloquent\Model;

class ExpedicaoProgramacao extends Model
{
    public const TIPO_PROGRAMADA = 'PROGRAMADA';
    public const TIPO_OPORTUNIDADE = 'OPORTUNIDADE';

    public const ORIGEM_PLANILHA_MANHA = 'PLANILHA_MANHA';
    public const ORIGEM_INCLUSAO_MANUAL = 'INCLUSAO_MANUAL';
    public const ORIGEM_IMPORTACAO_OPORTUNIDADE = 'IMPORTACAO_OPORTUNIDADE';

    protected $table = '_tb_expedicao_programacoes';

    protected $fillable = [
        'fo',
        'dt_sap',
        'agenda_entrega_em',
        'data_expedicao_em',
        'cidade_destino',
        'uf_destino',
        'codigo_cliente',
        'cliente',
        'transportadora',
        'tipo_veiculo',
        'tipo_carga',
        'possui_picking',
        'tipo_demanda',
        'origem_demanda',
        'status_previsao',
        'observacoes',
    ];

    protected $casts = [
        'agenda_entrega_em' => 'datetime',
        'data_expedicao_em' => 'datetime',
        'possui_picking' => 'boolean',
    ];

    public static function tiposDemanda(): array
    {
        return [
            self::TIPO_PROGRAMADA,
            self::TIPO_OPORTUNIDADE,
        ];
    }

    public static function origensDemanda(): array
    {
        return [
            self::ORIGEM_PLANILHA_MANHA,
            self::ORIGEM_INCLUSAO_MANUAL,
            self::ORIGEM_IMPORTACAO_OPORTUNIDADE,
        ];
    }

    public function getTipoDemandaLabelAttribute(): string
    {
        return $this->tipo_demanda === self::TIPO_OPORTUNIDADE
            ? 'Oportunidade'
            : 'Programada';
    }

    public function getTipoDemandaBadgeAttribute(): string
    {
        return $this->tipo_demanda === self::TIPO_OPORTUNIDADE
            ? 'badge-opportunity'
            : 'badge-programmed';
    }

    public function previsoes()
    {
        return $this->hasMany(ExpedicaoPrevisao::class, 'programacao_id');
    }

    public function ultimaPrevisao()
    {
        return $this->hasOne(ExpedicaoPrevisao::class, 'programacao_id')->latestOfMany();
    }
}
