<?php

namespace App\Services;

use App\Enums\AuditSeverity;
use App\Events\SecurityEvent;
use App\Http\Middleware\RequestCorrelationMiddleware;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecurityAuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'senha',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'cookie',
        'secret',
        'client_secret',
        'remember_token',
        '_token',
    ];

    /**
     * @param array<string, mixed> $payload
     */
    public function record(string $action, ?string $module = null, array $payload = [], ?Request $request = null): ?AuditLog
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return null;
            }

            $request = $request ?: request();
            $user = Auth::user();

            $log = AuditLog::create([
                'user_id' => $payload['user_id'] ?? $user?->getAuthIdentifier(),
                'action' => $action,
                'module' => $module ?? $this->moduleFromRequest($request),
                'severity' => $this->severityFor($action, $payload)->value,
                'correlation_id' => $payload['correlation_id'] ?? $request->attributes->get(RequestCorrelationMiddleware::CORRELATION_ID),
                'request_id' => $payload['request_id'] ?? $request->attributes->get(RequestCorrelationMiddleware::REQUEST_ID),
                'route' => $request->route()?->getName() ?: $request->path(),
                'method' => $request->method(),
                'response_status' => $payload['response_status'] ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload_resumo' => $this->sanitize($payload ?: $this->safeRequestSummary($request)),
            ]);

            app(SecurityMonitorService::class)->inspect($log);

            return $log;
        } catch (Throwable $exception) {
            Log::warning('security_audit_failed', [
                'action' => $action,
                'module' => $module,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function severityFor(string $action, array $payload): AuditSeverity
    {
        if (isset($payload['severity'])) {
            return AuditSeverity::tryFrom((string) $payload['severity']) ?? AuditSeverity::INFO;
        }

        return match ($action) {
            SecurityEvent::LOGIN_FAILURE,
            SecurityEvent::ACCESS_DENIED,
            SecurityEvent::PERMISSION_DENIED,
            SecurityEvent::RATE_LIMIT_TRIGGERED,
            'blocked_malicious_upload' => AuditSeverity::WARNING,
            default => AuditSeverity::INFO,
        };
    }

    public function recordSecurityEvent(SecurityEvent $event, ?Request $request = null): ?AuditLog
    {
        return $this->record($event->type, $event->context['module'] ?? 'security', $event->context, $request);
    }

    /**
     * @return array<string, mixed>
     */
    private function safeRequestSummary(Request $request): array
    {
        $summary = $request->except(self::SENSITIVE_KEYS);

        foreach ($request->allFiles() as $key => $file) {
            $summary[$key] = is_array($file)
                ? '[arquivos]'
                : [
                    'original_name' => $file?->getClientOriginalName(),
                    'mime' => $file?->getClientMimeType(),
                    'size' => $file?->getSize(),
                ];
        }

        return $summary;
    }

    private function sanitize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            return $value;
        }

        $clean = [];
        foreach ($value as $key => $item) {
            if ($this->isSensitiveKey((string) $key)) {
                $clean[$key] = '[REDACTED]';
                continue;
            }

            $clean[$key] = is_array($item) ? $this->sanitize($item) : $item;
        }

        return $clean;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = mb_strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($normalized, mb_strtolower($sensitiveKey))) {
                return true;
            }
        }

        return false;
    }

    private function moduleFromRequest(Request $request): string
    {
        return str($request->path())->before('/')->toString();
    }
}
