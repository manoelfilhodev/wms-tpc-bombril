<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Services\DeviceAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'senha',
        'token',
        'access_token',
        'refresh_token',
        'plainTextToken',
        'secret',
        'client_secret',
        'authorization',
        'cookie',
        'remember_token',
    ];

    public static function record(array $data): ?SystemLog
    {
        return app(self::class)->create($data);
    }

    public function create(array $data): ?SystemLog
    {
        try {
            if (! Schema::hasTable('_tb_system_logs')) {
                return null;
            }

            $request = request();
            $user = Auth::user();
            $actor = $this->actorContext($user);

            return SystemLog::create([
                'user_id' => $data['user_id'] ?? $actor['user_id'],
                'user_name' => $data['user_name'] ?? $actor['user_name'],
                'user_email' => $data['user_email'] ?? $actor['user_email'],
                'user_role' => $data['user_role'] ?? $actor['user_role'],
                'module' => $data['module'] ?? 'sistema',
                'action' => $data['action'] ?? 'acao_nao_informada',
                'description' => $data['description'] ?? 'Ação registrada pelo sistema.',
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => isset($data['entity_id']) ? (string) $data['entity_id'] : null,
                'old_values' => $this->sanitize($data['old_values'] ?? null),
                'new_values' => $this->sanitize($data['new_values'] ?? null),
                'ip_address' => $data['ip_address'] ?? $this->ip($request),
                'user_agent' => $data['user_agent'] ?? $request?->userAgent(),
                'device_id' => $data['device_id'] ?? $this->deviceId($request),
                'route' => $data['route'] ?? $this->route($request),
                'method' => $data['method'] ?? $request?->method(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('system_log_failed', [
                'message' => $exception->getMessage(),
                'module' => $data['module'] ?? null,
                'action' => $data['action'] ?? null,
            ]);

            return null;
        }
    }

    private function resolveUserRole(mixed $user): ?string
    {
        if (! $user) {
            return session('tipo') ?: session('nivel');
        }

        return trim(implode(' / ', array_filter([
            $user->tipo ?? null,
            $user->nivel ?? null,
        ]))) ?: null;
    }

    private function actorContext(mixed $user): array
    {
        return [
            'user_id' => $user?->getAuthIdentifier() ?? session('user_id'),
            'user_name' => $user?->nome ?? $user?->name ?? session('nome'),
            'user_email' => $user?->email,
            'user_role' => $this->resolveUserRole($user),
        ];
    }

    private function sanitize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
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

            $clean[$key] = is_array($item) || is_object($item)
                ? $this->sanitize($item)
                : $item;
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

    private function ip(?Request $request): ?string
    {
        return $request?->ip();
    }

    private function deviceId(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return $request->header('X-Device-Id')
            ?: $request->input('device_id')
            ?: $request->cookie(DeviceAuthorizationService::COOKIE_NAME)
            ?: $request->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME);
    }

    private function route(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return $request->route()?->getName() ?: $request->path();
    }
}
