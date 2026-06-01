<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'route',
        'method',
        'ip',
        'user_agent',
        'payload_resumo',
    ];

    protected $casts = [
        'payload_resumo' => 'array',
        'created_at' => 'datetime',
    ];
}
