<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitEtiqueta extends Model
{
    protected $table = '_tb_kit_etiquetas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_kit',
        'sku',
        'ean',
        'descricao',
        'ua',
        'lastro',
        'camada',
        'paletizacao',
        'numero_etiqueta',
        'total_etiquetas',
        'data_geracao',
        'quantidade'
    ];
}
