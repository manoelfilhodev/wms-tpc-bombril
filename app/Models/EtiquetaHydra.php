<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiquetaHydra extends Model
{
    protected $table = '_tb_etiquetas_sep_hydra_metais';
    public $timestamps = false;

    protected $fillable = [
        'fo',
        'remessa',
        'recebedor',
        'cliente',
        'produto',
        'qtd',
        'cidade',
        'uf',
        'doca',
        'usuario_id',
        'ip',
        'data_gerada',
    ];
}
