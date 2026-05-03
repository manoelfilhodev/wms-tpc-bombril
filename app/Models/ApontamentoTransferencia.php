<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApontamentoTransferencia extends Model
{
    use HasFactory;

    protected $table = '_tb_apontamentos_transferencia';

    protected $fillable = [
        'codigo_material',
        'quantidade',
        'status',
        'data',
        'user_id',
        'apontado_por',
        'unidade',
        'palete_uid'
    ];

    protected $dates = [
        'data',
        'created_at',
        'updated_at'
    ];

    /**
     * Relacionamento com Transferencia
     */
    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class, 'codigo_material', 'codigo_material');
    }

    /**
     * Relacionamento com usuário que apontou
     */
    public function apontadoPor()
    {
        return $this->belongsTo(User::class, 'apontado_por', 'id_user');
    }
}
