<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApontamentoPaleteStretch extends Model
{
    use SoftDeletes;

    protected $table = '_tb_apontamentos_paletes_stretch';

    protected $fillable = [
        'palete_codigo',
        'usuario_id',
        'unidade_id',
        'status',
        'origem',
        'observacao',
        'client_uuid',
        'device_id',
        'app_version',
        'apontado_em_app',
        'apontado_em_servidor',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'apontado_em_app' => 'datetime',
        'apontado_em_servidor' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_user');
    }
}
