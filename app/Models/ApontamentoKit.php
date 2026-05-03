<?php

// app/Models/ApontamentoKit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApontamentoKit extends Model
{
    protected $table = '_tb_apontamentos_kits';

    protected $fillable = [
        'codigo_material',
        'quantidade',
        'status',
        'data',
        'user_id',
        'unidade',
        'palete_uid',
        'apontado_por'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function apontadoPor()
    {
        return $this->belongsTo(User::class, 'apontado_por');
    }
}
