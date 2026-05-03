<?php

// app/Models/Equipamento.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipamento extends Model
{
    protected $table = '_tb_equipamentos';

    protected $fillable = [
        'tipo', 'modelo', 'patrimonio', 'numero_serie',
        'status', 'localizacao', 'responsavel',
        'data_aquisicao', 'observacoes',
    ];
}
