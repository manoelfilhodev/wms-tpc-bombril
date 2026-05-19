<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DeviceAuthorizationService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                false,
                false,
                false,
                'lax'
            ));
    }

    public function login(Request $request)
    {
        $deviceId = $request->cookie(DeviceAuthorizationService::COOKIE_NAME)
            ?: $request->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME);
        $deviceId = trim((string) $deviceId);
        $credentials = $request->only('email', 'password');

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return back()->with('error', 'Preencha todos os campos.');
        }

        if (! Auth::attempt($credentials)) {
            $usuario = User::where('email', $credentials['email'])->first();

            SystemLogService::record([
                'user_id' => $usuario?->id_user,
                'user_name' => $usuario?->nome,
                'user_email' => $credentials['email'],
                'user_role' => $usuario ? trim(($usuario->tipo ?? '') . ' / ' . ($usuario->nivel ?? '')) : null,
                'module' => 'login',
                'action' => 'login_invalido',
                'description' => 'Tentativa de login inválida.',
                'entity_type' => 'usuario',
                'entity_id' => $usuario?->id_user,
                'new_values' => ['email' => $credentials['email']],
            ]);

            if ($usuario && $usuario->id_user && $usuario->unidade_id) {
                $this->insertUserLog(
                    (int) $usuario->id_user,
                    (int) $usuario->unidade_id,
                    'login - falhou',
                    ['email' => $credentials['email']],
                    $request
                );
            }

            return back()->with('error', 'Login ou senha invalido');
        }

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
            'module' => 'login',
            'action' => 'login_realizado',
            'description' => 'Usuário realizou login no sistema.',
            'entity_type' => 'usuario',
            'entity_id' => $usuario->id_user,
            'new_values' => ['email' => $usuario->email, 'device_id' => $deviceId ?: null],
        ]);

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
                false,
                false,
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
            'module' => 'login',
            'action' => 'logout',
            'description' => 'Usuário encerrou a sessão manualmente.',
            'entity_type' => 'usuario',
            'entity_id' => $user->id_user,
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($this->microsoftLogoutUrl());
    }

public function apiLogin(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (empty($credentials['email']) || empty($credentials['password'])) {
        return response()->json(['message' => 'Preencha todos os campos.'], 422);
    }

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

    private function microsoftLogoutUrl(): string
    {
        $postLogoutRedirectUri = config('services.microsoft.post_logout_redirect_uri', route('login'));

        return 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri='
            . urlencode($postLogoutRedirectUri);
    }
}
