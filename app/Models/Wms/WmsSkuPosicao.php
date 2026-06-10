<?php

namespace App\Models\Wms;

use Illuminate\Database\Eloquent\Model;

class WmsSkuPosicao extends Model
{
    protected $table = '_tb_wms_sku_posicoes';

    protected $fillable = [
        'sku_id',
        'posicao_id',
        'sku',
        'endereco',
        'qtd_padrao',
        'prioridade',
        'ativo',
    ];

    protected $casts = [
        'qtd_padrao' => 'decimal:3',
        'prioridade' => 'integer',
        'ativo' => 'boolean',
    ];

    public function skuCadastro()
    {
        return $this->belongsTo(WmsSku::class, 'sku_id');
    }

    public function posicao()
    {
        return $this->belongsTo(WmsPosicao::class, 'posicao_id');
    }
}
