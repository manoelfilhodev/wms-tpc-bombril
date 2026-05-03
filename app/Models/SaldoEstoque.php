<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaldoEstoque extends Model
{
    use HasFactory;

    protected $table = '_tb_saldo_estoque';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'sku_id',
        'material_id',
        'posicao_id',
        'unidade_id',
        'quantidade',
        'data_entrada',
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
