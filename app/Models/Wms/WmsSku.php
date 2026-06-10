<?php

namespace App\Models\Wms;

use Illuminate\Database\Eloquent\Model;

class WmsSku extends Model
{
    protected $table = '_tb_wms_skus';

    protected $fillable = [
        'sku',
        'descricao',
        'peso_kg',
        'classe_peso',
        'classe_cubagem',
        'curva_abc',
        'ativo',
    ];

    protected $casts = [
        'peso_kg' => 'decimal:3',
        'ativo' => 'boolean',
    ];

    public function posicoes()
    {
        return $this->hasMany(WmsSkuPosicao::class, 'sku_id');
    }
}
