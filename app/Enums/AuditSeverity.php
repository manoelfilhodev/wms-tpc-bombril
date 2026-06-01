<?php

namespace App\Enums;

enum AuditSeverity: string
{
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';
}
