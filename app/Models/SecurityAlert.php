<?php

namespace App\Models;

use App\Enums\AuditSeverity;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'severity',
        'title',
        'description',
        'correlation_id',
        'request_id',
        'context',
    ];

    protected $casts = [
        'severity' => AuditSeverity::class,
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
