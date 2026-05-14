<?php

namespace App\Models\Expedicao;

use Illuminate\Database\Eloquent\Model;

class ExpedicaoPrevisao extends Model
{
    protected $table = '_tb_expedicao_previsoes';

    protected $fillable = [
        'programacao_id',
        'fo',
        'score_operacional',
        'tempo_separacao_min',
        'tempo_conferencia_min',
        'tempo_carregamento_min',
        'tempo_viagem_min',
        'tempo_total_min',
        'previsao_chegada_doca',
        'previsao_inicio_separacao',
        'previsao_inicio_conferencia',
        'previsao_inicio_carregamento',
        'previsao_saida_caminhao',
        'risco_operacional',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'score_operacional' => 'decimal:2',
        'previsao_chegada_doca' => 'datetime',
        'previsao_inicio_separacao' => 'datetime',
        'previsao_inicio_conferencia' => 'datetime',
        'previsao_inicio_carregamento' => 'datetime',
        'previsao_saida_caminhao' => 'datetime',
    ];

    public function programacao()
    {
        return $this->belongsTo(ExpedicaoProgramacao::class, 'programacao_id');
    }
}