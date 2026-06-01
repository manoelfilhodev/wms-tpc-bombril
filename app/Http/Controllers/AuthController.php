<?php

namespace App\Http\Controllers;

use App\Events\SecurityEvent;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\DeviceAuthorizationService;
use App\Services\SecurityAuditService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $deviceId = $request->cookie(DeviceAuthorizationService::COOKIE_NAME)
            ?: $request->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME);
        $deviceId = trim((string) $deviceId);

        if (! $deviceId) {
            $deviceId = (string) str()->uuid();
        }

        $deviceRegistered = app(DeviceAuthorizationService::class)
            ->isActiveDeviceRegistered($deviceId, 'web');

        return response()
            ->view('auth.login', [
                'deviceId' => $deviceId,
                'showDeviceId' => ! $deviceRegistered,
                'deviceCookieName' => DeviceAuthorizationService::COOKIE_NAME,
                'legacyDeviceCookieName' => DeviceAuthorizationService::LEGACY_COOKIE_NAME,
            ])
            ->withCookie(cookie(
                DeviceAuthorizationService::COOKIE_NAME,
                $deviceId,
                60 * 24 * 365,
                '/',
                null,
                app()->environment('production'),
                true,
                false,
                'lax'
            ));
    }

    public function login(LoginRequest $request)
    {
        $deviceId = $request->cookie(DeviceAuthorizationService::COOKIE_NAME)
            ?: $request->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME);
        $deviceId = trim((string) $deviceId);
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            $usuario = User::where('email', $credentials['email'])->first();

            if ($usuario && $usuario->id_user && $usuario->unidade_id) {
                $this->insertUserLog(
                    (int) $usuario->id_user,
                    (int) $usuario->unidade_id,
                    'login - falhou',
                    ['email' => $credentials['email']],
                    $request
                );
            }

            app(SecurityAuditService::class)->recordSecurityEvent(
                new SecurityEvent(SecurityEvent::LOGIN_FAILURE, ['module' => 'login']),
                $request
            );

            return back()->with('error', 'Credenciais invalidas.');
        }

        $this->invalidateOtherSessions((int) Auth::id(), $request->session()->getId());
        $request->session()->regenerate();

        $usuario = Auth::user();

        session([
            'user_id' => $usuario->id_user,
            'nome' => $usuario->nome,
            'tipo' => $usuario->tipo,
            'unidade' => $usuario->unidade_id,
            'nivel' => $usuario->nivel,
        ]);

        if ($usuario->id_user && $usuario->unidade_id) {
            $this->insertUserLog(
                (int) $usuario->id_user,
                (int) $usuario->unidade_id,
                'login - sucesso',
                ['email' => $usuario->email, 'device_id' => $deviceId ?: null],
                $request
            );
        }

        SystemLogService::record([
            'user_id' => $usuario->id_user,
            'user_name' => $usuario->nome,
            'user_email' => $usuario->email,
            'user_role' => $this->systemLogUserRole($usuario),
            'module' => 'login',
            'action' => 'login_realizado',
            'description' => 'Usuário realizou login no sistema.',
            'entity_type' => 'usuario',
            'entity_id' => $usuario->id_user,
            'new_values' => ['email' => $usuario->email, 'device_id' => $deviceId ?: null],
        ]);

        app(SecurityAuditService::class)->recordSecurityEvent(
            new SecurityEvent(SecurityEvent::LOGIN_SUCCESS, [
                'module' => 'login',
                'user_id' => $usuario->id_user,
            ]),
            $request
        );

        $redirect = $usuario->tipo === 'operador'
            ? redirect()->route('painel.operador')
            : redirect()->route('demandas.dashboardOperacional');

        return $deviceId
            ? $redirect->withCookie(cookie(
                DeviceAuthorizationService::COOKIE_NAME,
                $deviceId,
                60 * 24 * 365,
                '/',
                null,
                app()->environment('production'),
                true,
                false,
                'lax'
            ))
            : $redirect;
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('info', 'Sessao ja encerrada.');
        }

        if ($user->id_user && $user->unidade_id) {
            $this->insertUserLog(
                (int) $user->id_user,
                (int) $user->unidade_id,
                'logout',
                ['message' => 'Usuario saiu manualmente do sistema.'],
                $request
            );
        }

        SystemLogService::record([
            'user_id' => $user->id_user,
            'user_name' => $user->nome,
            'user_email' => $user->email,
            'user_role' => $this->systemLogUserRole($user),
            'module' => 'login',
            'action' => 'logout',
            'description' => 'Usuário encerrou a sessão manualmente.',
            'entity_type' => 'usuario',
            'entity_id' => $user->id_user,
        ]);

        app(SecurityAuditService::class)->record('logout', 'login', [
            'user_id' => $user->id_user,
        ], $request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($this->microsoftLogoutUrl());
    }

public function apiLogin(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email:rfc', 'max:100'],
        'password' => ['required', 'string', 'max:255'],
    ], [
        'email.required' => 'Informe o e-mail.',
        'email.email' => 'Informe um e-mail valido.',
        'password.required' => 'Informe a senha.',
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        if ($user && $user->id_user && $user->unidade_id) {
            $this->insertUserLog(
                (int) $user->id_user,
                (int) $user->unidade_id,
                'login_api - falhou',
                ['email' => $credentials['email']],
                $request
            );
        }

        app(SecurityAuditService::class)->recordSecurityEvent(
            new SecurityEvent(SecurityEvent::LOGIN_FAILURE, ['module' => 'login']),
            $request
        );

        return response()->json(['message' => 'Credenciais invalidas'], 401);
    }

    // 🔐 GERAR TOKEN
    $token = $user->createToken('app_token')->plainTextToken;

    // 🧠 CAPTURAR DEVICE ID (HEADER OU BODY)
    $deviceId = $request->header('X-Device-Id') 
        ?? $request->input('device_id');

    $device = null;

    if ($deviceId) {
        $device = app(\App\Services\DeviceAuthorizationService::class)
            ->findAuthorizedDevice($user, $deviceId, 'app');
    }

    // 📝 LOGS
    $this->insertUserLog(
        (int) $user->id_user,
        (int) $user->unidade_id,
        'login_app - sucesso',
        ['email' => $user->email, 'device_id' => $deviceId],
        $request
    );

    $this->insertUserLog(
        (int) $user->id_user,
        (int) $user->unidade_id,
        'login - sucesso',
        ['email' => $user->email],
        $request
    );

    SystemLogService::record([
        'user_id' => $user->id_user,
        'user_name' => $user->nome,
        'user_email' => $user->email,
        'user_role' => $this->systemLogUserRole($user),
        'module' => 'login',
        'action' => 'login_api_realizado',
        'description' => 'Usuário realizou login via API/app.',
        'entity_type' => 'usuario',
        'entity_id' => $user->id_user,
        'device_id' => $deviceId ?: null,
        'new_values' => ['email' => $user->email, 'device_id' => $deviceId ?: null],
    ]);

    app(SecurityAuditService::class)->recordSecurityEvent(
        new SecurityEvent(SecurityEvent::LOGIN_SUCCESS, [
            'module' => 'login',
            'user_id' => $user->id_user,
        ]),
        $request
    );

    // 🚀 RESPOSTA FINAL
    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id_user,
            'nome' => $user->nome,
            'tipo' => $user->tipo,
            'unidade' => $user->unidade_id,
            'nivel' => $user->nivel,
        ],
        'device_authorized' => (bool) $device,
        'device_id' => $deviceId, // opcional (bom pra debug)
    ]);
}

    private function insertUserLog(int $usuarioId, int $unidadeId, string $acao, array $dados, Request $request): void
    {
        DB::table('_tb_user_logs')->insert([
            'usuario_id' => $usuarioId,
            'unidade_id' => $unidadeId,
            'acao' => $acao,
            'dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_address' => $request->ip(),
            'navegador' => $request->header('User-Agent'),
            'created_at' => now(),
        ]);
    }

    private function invalidateOtherSessions(int $userId, string $currentSessionId): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    private function microsoftLogoutUrl(): string
    {
        $postLogoutRedirectUri = config('services.microsoft.post_logout_redirect_uri', route('login'));

        return 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri='
            . urlencode($postLogoutRedirectUri);
    }

    private function systemLogUserRole(User $user): ?string
    {
        return trim(implode(' / ', array_filter([
            $user->tipo ?? null,
            $user->nivel ?? null,
        ]))) ?: null;
    }
}
