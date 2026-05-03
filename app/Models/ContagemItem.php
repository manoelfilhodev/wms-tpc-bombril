<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContagemItem extends Model
{
    use HasFactory;

    protected $table = '_tb_contagem_itens';

    protected $fillable = [
        'codigo_material',
        'quantidade',
        'usuario_id',
        'unidade_id',
        'data_contagem',
    ];

    public function material()
    {
        return $this->belongsTo(ItemContagem::class, 'codigo_material', 'codigo_material');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_user');
    }
}
