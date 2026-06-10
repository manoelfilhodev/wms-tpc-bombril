<?php

namespace App\Models\Wms;

use Illuminate\Database\Eloquent\Model;

class WmsPosicao extends Model
{
    protected $table = '_tb_wms_posicoes';

    protected $fillable = [
        'bloco',
        'rua',
        'posicao',
        'endereco',
        'lado',
        'sequencia_rota',
        'status',
        'ativo',
    ];

    protected $casts = [
        'sequencia_rota' => 'integer',
        'ativo' => 'boolean',
    ];

    public function skuPosicoes()
    {
        return $this->hasMany(WmsSkuPosicao::class, 'posicao_id');
    }
}
