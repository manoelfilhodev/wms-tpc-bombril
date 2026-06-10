<?php

namespace App\Models\Wms;

use Illuminate\Database\Eloquent\Model;

class WmsSeparacaoFolha extends Model
{
    protected $table = '_tb_wms_separacao_folhas';

    protected $fillable = [
        'geracao_id',
        'numero_folha',
        'separador_numero',
        'titulo',
        'rua',
        'curva_abc',
        'total_skus',
        'total_quantidade',
        'peso_estimado',
        'status',
    ];

    protected $casts = [
        'numero_folha' => 'integer',
        'separador_numero' => 'integer',
        'total_skus' => 'integer',
        'total_quantidade' => 'decimal:3',
        'peso_estimado' => 'decimal:3',
    ];

    public function geracao()
    {
        return $this->belongsTo(WmsSeparacaoGeracao::class, 'geracao_id');
    }

    public function itens()
    {
        return $this->hasMany(WmsSeparacaoItem::class, 'folha_id');
    }
}
