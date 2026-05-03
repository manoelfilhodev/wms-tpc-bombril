<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = '_tb_unidades';

    public $timestamps = false;

    protected $fillable = ['nome'];

    // Você pode adicionar relacionamentos aqui se quiser
}
