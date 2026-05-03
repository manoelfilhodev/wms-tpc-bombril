<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitMontagem extends Model
{
    protected $table = '_tb_kit_montagem';

    protected $fillable = [
        'codigo_material',
        'quantidade_programada',
        'quantidade_produzida',
        'usuario_id',
        'unidade_id',
        'data_montagem',
        'created_at',
    ];

    public $timestamps = false; // já estamos tratando created_at manualmente
}
