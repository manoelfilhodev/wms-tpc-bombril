<?php

namespace App\Models\Setores;

use Illuminate\Database\Eloquent\Model;

class Armazenagem extends Model
{
    protected $table = '_tb_armazenagem';

    protected $fillable = [
        'sku',
        'quantidade',
        'endereco',
        'observacoes',
        'usuario_id',
        'unidade_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'data_armazenagem' => 'datetime',
    ];
}