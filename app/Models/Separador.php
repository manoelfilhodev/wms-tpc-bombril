<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Separador extends Model
{
    protected $table = '_tb_separadores';

    protected $fillable = [
        'chapa',
        'nome',
        'cargo',
        'turno',
    ];
}
