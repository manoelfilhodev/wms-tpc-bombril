<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemContagem extends Model
{
    use HasFactory;

    protected $table = '_tb_itens_contagem';

    protected $fillable = [
        'codigo_material',
        'descricao',
    ];

    public $timestamps = false;
}
