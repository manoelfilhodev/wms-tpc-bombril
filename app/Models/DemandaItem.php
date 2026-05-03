<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaItem extends Model
{
    use HasFactory;

    protected $table = '_tb_demanda_itens';

    protected $fillable = [
        'demanda_id',
        'sku',
        'sku_normalizado',
        'descricao',
        'unidade_medida',
        'sobra',
        'bloqueado',
    ];
}

