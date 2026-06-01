<?php

namespace App\Services;

use App\Enums\AuditSeverity;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Schema;

class SecurityMonitorService
{
    public function inspect(AuditLog $log): void
    {
        if (! Schema::hasTable('security_alerts')) {
            return;
        }

        $this->detectLoginFailures($log);
        $this->detectRateLimits($log);
        $this->detectPermissionDenied($log);
        $this->detectBlockedUploads($log);
    }

    private function detectLoginFailures(AuditLog $log): void
    {
        if ($log->action !== 'LOGIN_FAILURE') {
            return;
        }

        $count = AuditLog::where('action', 'LOGIN_FAILURE')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->when($log->ip, fn ($query) => $query->where('ip', $log->ip))
            ->count();

        if ($count >= 10) {
            $this->alert('LOGIN_FAILURE_SPIKE', AuditSeverity::CRITICAL, 'Muitas falhas de login', $log, [
                'count' => $count,
                'window_minutes' => 5,
            ]);
        }
    }

    private function detectRateLimits(AuditLog $log): void
    {
        if ($log->action !== 'RATE_LIMIT_TRIGGERED') {
            return;
        }

        $count = AuditLog::where('action', 'RATE_LIMIT_TRIGGERED')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->when($log->ip, fn ($query) => $query->where('ip', $log->ip))
            ->count();

        if ($count >= 20) {
            $this->alert('RATE_LIMIT_SPIKE', AuditSeverity::WARNING, 'Muitos rate limits acionados', $log, [
                'count' => $count,
                'window_minutes' => 10,
            ]);
        }
    }

    private function detectPermissionDenied(AuditLog $log): void
    {
        if (! in_array($log->action, ['PERMISSION_DENIED', 'ACCESS_DENIED'], true)) {
            return;
        }

        $count = AuditLog::whereIn('action', ['PERMISSION_DENIED', 'ACCESS_DENIED'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->when($log->user_id, fn ($query) => $query->where('user_id', $log->user_id))
            ->when(! $log->user_id && $log->ip, fn ($query) => $query->where('ip', $log->ip))
            ->count();

        if ($count >= 5) {
            $this->alert('ACCESS_DENIED_SPIKE', AuditSeverity::WARNING, 'Multiplos acessos negados', $log, [
                'count' => $count,
                'window_minutes' => 10,
            ]);
        }
    }

    private function detectBlockedUploads(AuditLog $log): void
    {
        if ($log->action !== 'blocked_malicious_upload') {
            return;
        }

        $count = AuditLog::where('action', 'blocked_malicious_upload')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->when($log->ip, fn ($query) => $query->where('ip', $log->ip))
            ->count();

        if ($count >= 3) {
            $this->alert('BLOCKED_UPLOAD_SPIKE', AuditSeverity::CRITICAL, 'Uploads perigosos repetidos', $log, [
                'count' => $count,
                'window_minutes' => 10,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function alert(string $type, AuditSeverity $severity, string $title, AuditLog $log, array $context): void
    {
        $recentExists = SecurityAlert::where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->when($log->ip, fn ($query) => $query->where('context->ip', $log->ip))
            ->exists();

        if ($recentExists) {
            return;
        }

        SecurityAlert::create([
            'type' => $type,
            'severity' => $severity->value,
            'title' => $title,
            'description' => 'Alerta gerado automaticamente pelo monitor de seguranca.',
            'correlation_id' => $log->correlation_id,
            'request_id' => $log->request_id,
            'context' => array_merge($context, [
                'ip' => $log->ip,
                'user_id' => $log->user_id,
                'route' => $log->route,
            ]),
        ]);
    }
}
