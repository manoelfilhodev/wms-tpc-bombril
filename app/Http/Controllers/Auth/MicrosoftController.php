<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceAuthorizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class MicrosoftController extends Controller
{
    public function __construct(private readonly DeviceAuthorizationService $deviceAuthorization)
    {
    }

    public function redirectToProvider(): RedirectResponse
    {
        if (! $this->hasTenantConfigured()) {
            return redirect()->route('login')->with('error', 'Login Microsoft ainda nao configurado para o tenant Systex.');
        }

        $deviceId = request()->cookie(DeviceAuthorizationService::COOKIE_NAME)
            ?: request()->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME)
            ?: (string) str()->uuid();

        return Socialite::driver('graph')
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect()
            ->withCookie(cookie(
                DeviceAuthorizationService::COOKIE_NAME,
                $deviceId,
                60 * 24 * 365,
                '/',
                null,
                app()->environment('production'),
                false,
                false,
                'lax'
            ));
    }

    public function handleProviderCallback(Request $request): RedirectResponse
    {
        try {
            if (! $this->hasTenantConfigured()) {
                return redirect()->route('login')->with('error', 'Login Microsoft ainda nao configurado para o tenant Systex.');
            }

            $microsoftUser = Socialite::driver('graph')->user();

            $email = $microsoftUser->getEmail();
            $name = $microsoftUser->getName() ?: 'Usuario Microsoft';
            $azureId = $microsoftUser->getId();

            Log::info('login_microsoft - tentativa', [
                'email' => $email,
                'azure_id' => $azureId,
                'ip' => $request->ip(),
            ]);

            if (! $email) {
                Log::warning('login_microsoft - bloqueado_sem_email', ['ip' => $request->ip()]);

                return redirect()->route('login')->with('error', 'Nao foi possivel obter o e-mail da conta Microsoft.');
            }

            $email = mb_strtolower($email);

            if (! $azureId) {
                Log::warning('login_microsoft - bloqueado_sem_azure_id', [
                    'email' => $email,
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('login')->with('error', 'Nao foi possivel obter o identificador Azure da conta Microsoft.');
            }

            if (! $this->emailBelongsToAllowedDomain($email)) {
                Log::warning('login_microsoft - bloqueado_dominio', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('login')->with('error', 'Esta conta Microsoft nao pertence a rede Systex autorizada.');
            }

            $user = User::where('email', $email)->first();

            if (! $user) {
                if (! config('services.microsoft.auto_provision_users')) {
                    Log::warning('login_microsoft - bloqueado_usuario_nao_cadastrado', [
                        'email' => $email,
                        'azure_id' => $azureId,
                        'ip' => $request->ip(),
                    ]);

                    return redirect()->route('login')->with('error', 'Usuario Microsoft autorizado, mas ainda nao cadastrado no WMS.');
                }

                $defaultUnidadeId = config('services.microsoft.default_unidade_id');
                $defaultRole = $this->defaultProvisionRole();

                if (! $defaultUnidadeId) {
                    return redirect()->route('login')->with('error', 'Unidade padrao para usuarios Microsoft nao configurada.');
                }

                $userId = DB::table('_tb_usuarios')->insertGetId([
                    'nome' => $name,
                    'email' => $email,
                    'azure_id' => $azureId,
                    'password' => bcrypt(str()->random(32)),
                    'unidade_id' => (int) $defaultUnidadeId,
                    'tipo' => $defaultRole,
                    'nivel' => strtoupper($defaultRole),
                    'status' => 'ativo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user = User::where('id_user', $userId)->first();

                Log::info('login_microsoft - usuario_auto_provisionado', [
                    'usuario_id' => $user?->id_user,
                    'email' => $email,
                    'azure_id' => $azureId,
                    'tipo' => $defaultRole,
                    'status' => 'ativo',
                    'ip' => $request->ip(),
                ]);
            }

            $userByAzureId = User::where('azure_id', $azureId)->first();

            if ($userByAzureId && $userByAzureId->id_user !== $user->id_user) {
                $this->insertMicrosoftUserLog($user, 'login_microsoft - bloqueado_azure_id_em_outro_usuario', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'linked_user_id' => $userByAzureId->id_user,
                ], $request);

                return redirect()->route('login')->with('error', 'Conta Microsoft ja vinculada a outro usuario do WMS.');
            }

            if ($user->azure_id && ! hash_equals((string) $user->azure_id, (string) $azureId)) {
                $this->insertMicrosoftUserLog($user, 'login_microsoft - bloqueado_azure_id_divergente', [
                    'email' => $email,
                    'azure_id' => $azureId,
                ], $request);

                return redirect()->route('login')->with('error', 'Conta Microsoft diferente da vinculada ao usuario WMS.');
            }

            if ($user->status !== 'ativo') {
                $this->insertMicrosoftUserLog($user, 'login_microsoft - bloqueado_usuario_inativo', [
                    'email' => $email,
                    'azure_id' => $azureId,
                ], $request);

                return redirect()->route('login')->with('error', 'Usuario inativo no WMS.');
            }

            if (! $user->azure_id) {
                $user->forceFill(['azure_id' => $azureId])->save();
            }

            $deviceId = $request->cookie(DeviceAuthorizationService::COOKIE_NAME)
                ?: $request->cookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME);

            if ($this->deviceAuthorization->requiresDeviceValidation($user)) {
                $device = $this->deviceAuthorization->findAuthorizedDevice($user, $deviceId, 'web');

                if (! $device) {
                    $this->insertMicrosoftUserLog($user, 'login_microsoft - bloqueado_dispositivo_nao_autorizado', [
                        'email' => $email,
                        'azure_id' => $azureId,
                        'device_id' => $deviceId,
                    ], $request);

                    return redirect()->route('login')->with('error', 'Dispositivo nao autorizado para acesso operacional.');
                }

                $this->deviceAuthorization->touchLastAccess($device);
                $this->insertMicrosoftUserLog($user, 'login_microsoft - dispositivo_autorizado', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'device_id' => $deviceId,
                    'dispositivo_id' => $device->id,
                ], $request);
            } else {
                $this->insertMicrosoftUserLog($user, 'login_microsoft - admin_sem_validacao_dispositivo', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'device_id' => $deviceId,
                ], $request);
            }

            Auth::login($user);
            $request->session()->regenerate();

            session([
                'user_id' => $user->id_user,
                'nome' => $user->nome,
                'tipo' => $user->tipo,
                'unidade' => $user->unidade_id,
                'nivel' => $user->nivel,
            ]);

            $this->insertMicrosoftUserLog($user, 'login_microsoft - sucesso', [
                'email' => $email,
                'azure_id' => $azureId,
            ], $request);

            return $user->tipo === 'operador'
                ? redirect()->intended(route('painel.operador'))
                : redirect()->intended(route('demandas.dashboardOperacional'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'Falha no login com Microsoft.');
        }
    }

    private function hasTenantConfigured(): bool
    {
        $tenantId = config('services.microsoft.tenant_id');

        return filled($tenantId) && $tenantId !== 'YOUR_MICROSOFT_TENANT_ID';
    }

    private function emailBelongsToAllowedDomain(string $email): bool
    {
        $domain = strtolower(str($email)->afterLast('@')->toString());
        $allowedDomains = array_map('strtolower', config('services.microsoft.allowed_domains', []));

        return $domain !== '' && in_array($domain, $allowedDomains, true);
    }

    private function defaultProvisionRole(): string
    {
        $role = strtolower(trim((string) config('services.microsoft.default_role', 'OPERADOR')));

        return in_array($role, ['operador'], true) ? $role : 'operador';
    }

    private function insertMicrosoftUserLog(User $user, string $acao, array $dados, Request $request): void
    {
        Log::info($acao, array_merge($dados, [
            'usuario_id' => $user->id_user,
            'ip' => $request->ip(),
        ]));

        if (! $user->id_user || ! $user->unidade_id) {
            return;
        }

        DB::table('_tb_user_logs')->insert([
            'usuario_id' => $user->id_user,
            'unidade_id' => $user->unidade_id,
            'acao' => $acao,
            'dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_address' => $request->ip(),
            'navegador' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
