<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = '_tb_transferencias';

    protected $fillable = [
        'codigo_material',
        'quantidade_programada',
        'quantidade_produzida',
        'usuario_id',
        'unidade_id',
        'data_transferencia',
        'programado_por',
        'programado_em',
        'apontado_por',
        'apontado_em'
    ];

    protected $dates = [
        'data_transferencia',
        'programado_em',
        'apontado_em',
        'created_at',
        'updated_at'
    ];

    /**
     * Relacionamento com os apontamentos de transferência
     */
    public function apontamentos()
    {
        return $this->hasMany(ApontamentoTransferencia::class, 'codigo_material', 'codigo_material');
    }
}
