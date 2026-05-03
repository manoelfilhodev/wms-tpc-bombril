<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MicrosoftApiController extends Controller
{
    public function __construct(
        private readonly DeviceAuthorizationService $deviceAuthorization
    ) {}

    public function loginFromApp(Request $request)
    {
        $request->validate([
            'access_token' => 'nullable|string',
            'id_token' => 'nullable|string',
            'microsoft_token' => 'nullable|string',
            'device_id' => 'nullable|string|max:150',
            'platform' => 'nullable|string|max:50',
        ]);

        $accessToken = $request->input('access_token') ?: $request->input('microsoft_token');
        $idToken = $request->input('id_token');

        if (! $accessToken) {
            return response()->json([
                'message' => 'Access token Microsoft não informado.',
            ], 422);
        }

        if ($idToken && ! $this->tokenBelongsToConfiguredTenant($idToken)) {
            return response()->json([
                'message' => 'Conta Microsoft fora do tenant autorizado.',
            ], 401);
        }

        $idClaims = $idToken ? $this->decodeJwtPayload($idToken) : [];

        $graphResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://graph.microsoft.com/v1.0/me');

        if ($graphResponse->failed()) {
            Log::warning('login_microsoft_api - token_graph_invalido', [
                'status' => $graphResponse->status(),
                'body' => $graphResponse->body(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Não foi possível validar o usuário Microsoft.',
            ], 401);
        }

        $microsoftUser = $graphResponse->json();

        $email = $microsoftUser['mail']
            ?? $microsoftUser['userPrincipalName']
            ?? $idClaims['preferred_username']
            ?? $idClaims['email']
            ?? null;

        $name = $microsoftUser['displayName']
            ?? $idClaims['name']
            ?? 'Usuário Microsoft';

        $azureId = $microsoftUser['id']
            ?? $idClaims['oid']
            ?? $idClaims['sub']
            ?? null;

        if (! $email) {
            return response()->json([
                'message' => 'Não foi possível obter o e-mail da conta Microsoft.',
            ], 422);
        }

        if (! $azureId) {
            return response()->json([
                'message' => 'Não foi possível obter o identificador Azure da conta Microsoft.',
            ], 422);
        }

        $email = mb_strtolower(trim($email));

        Log::info('login_microsoft_api - tentativa', [
            'email' => $email,
            'azure_id' => $azureId,
            'platform' => $request->input('platform'),
            'ip' => $request->ip(),
        ]);

        if (! $this->emailBelongsToAllowedDomain($email)) {
            Log::warning('login_microsoft_api - bloqueado_dominio', [
                'email' => $email,
                'azure_id' => $azureId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Conta Microsoft fora do domínio autorizado.',
            ], 401);
        }

        $user = DB::table('_tb_usuarios')
            ->where('email', $email)
            ->first();

        if (! $user) {
            if (! config('services.microsoft.auto_provision_users')) {
                Log::warning('login_microsoft_api - bloqueado_usuario_nao_cadastrado', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Usuário Microsoft autorizado, mas ainda não cadastrado no WMS.',
                ], 403);
            }

            $defaultUnidadeId = config('services.microsoft.default_unidade_id');
            $defaultRole = $this->defaultProvisionRole();

            if (! $defaultUnidadeId) {
                return response()->json([
                    'message' => 'Unidade padrão para usuários Microsoft não configurada.',
                ], 500);
            }

            $idUser = DB::table('_tb_usuarios')->insertGetId([
                'nome' => $name,
                'email' => $email,
                'azure_id' => $azureId,
                'password' => bcrypt(Str::random(32)),
                'unidade_id' => (int) $defaultUnidadeId,
                'tipo' => $defaultRole,
                'nivel' => strtoupper($defaultRole),
                'status' => 'ativo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = DB::table('_tb_usuarios')
                ->where('id_user', $idUser)
                ->first();

            Log::info('login_microsoft_api - usuario_auto_provisionado', [
                'usuario_id' => $user?->id_user,
                'email' => $email,
                'azure_id' => $azureId,
                'ip' => $request->ip(),
            ]);
        }

        $userByAzureId = DB::table('_tb_usuarios')
            ->where('azure_id', $azureId)
            ->first();

        if ($userByAzureId && (int) $userByAzureId->id_user !== (int) $user->id_user) {
            $this->insertMicrosoftUserLog($user, 'login_microsoft_api - bloqueado_azure_id_em_outro_usuario', [
                'email' => $email,
                'azure_id' => $azureId,
                'linked_user_id' => $userByAzureId->id_user,
            ], $request);

            return response()->json([
                'message' => 'Conta Microsoft já vinculada a outro usuário do WMS.',
            ], 403);
        }

        if (! empty($user->azure_id) && ! hash_equals((string) $user->azure_id, (string) $azureId)) {
            $this->insertMicrosoftUserLog($user, 'login_microsoft_api - bloqueado_azure_id_divergente', [
                'email' => $email,
                'azure_id' => $azureId,
            ], $request);

            return response()->json([
                'message' => 'Conta Microsoft diferente da vinculada ao usuário WMS.',
            ], 403);
        }

        if (($user->status ?? null) !== 'ativo') {
            $this->insertMicrosoftUserLog($user, 'login_microsoft_api - bloqueado_usuario_inativo', [
                'email' => $email,
                'azure_id' => $azureId,
            ], $request);

            return response()->json([
                'message' => 'Usuário inativo no WMS.',
            ], 403);
        }

        $eloquentUser = User::where('email', $email)->first();

        if (! $eloquentUser) {
            return response()->json([
                'message' => 'Usuário encontrado na base, mas não localizado pelo Model User.',
            ], 500);
        }

        if (empty($eloquentUser->azure_id)) {
            $eloquentUser->forceFill([
                'azure_id' => $azureId,
            ])->save();
        }

        $deviceId = $request->string('device_id')->trim()->value() ?: null;

        if ($this->deviceAuthorization->requiresDeviceValidation($eloquentUser)) {
            $device = $this->deviceAuthorization->findAuthorizedDevice(
                $eloquentUser,
                $deviceId,
                'app'
            );

            if (! $device) {
                $this->insertMicrosoftUserLog($user, 'login_microsoft_api - bloqueado_dispositivo_nao_autorizado', [
                    'email' => $email,
                    'azure_id' => $azureId,
                    'device_id' => $deviceId,
                ], $request);

                return response()->json([
                    'message' => 'Dispositivo não autorizado para acesso operacional.',
                ], 403);
            }

            $this->deviceAuthorization->touchLastAccess($device);

            $this->insertMicrosoftUserLog($user, 'login_microsoft_api - dispositivo_autorizado', [
                'email' => $email,
                'azure_id' => $azureId,
                'device_id' => $deviceId,
                'dispositivo_id' => $device->id,
            ], $request);
        } else {
            $this->insertMicrosoftUserLog($user, 'login_microsoft_api - admin_sem_validacao_dispositivo', [
                'email' => $email,
                'azure_id' => $azureId,
                'device_id' => $deviceId,
            ], $request);
        }

        $token = $eloquentUser->createToken('mobile-app')->plainTextToken;

        $this->insertMicrosoftUserLog($user, 'login_microsoft_api - sucesso', [
            'email' => $email,
            'azure_id' => $azureId,
        ], $request);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id_user,
                'id_user' => $user->id_user,
                'nome' => $user->nome,
                'name' => $user->nome,
                'email' => $user->email,
                'tipo' => $user->tipo,
                'nivel' => $user->nivel ?? $user->tipo,
                'unidade' => $user->unidade_id ?? null,
            ],
            'permissions' => [],
        ]);
    }

    private function tokenBelongsToConfiguredTenant(string $token): bool
    {
        $tenantId = config('services.microsoft.tenant_id');

        if (! filled($tenantId) || $tenantId === 'YOUR_MICROSOFT_TENANT_ID') {
            return false;
        }

        $claims = $this->decodeJwtPayload($token);

        return isset($claims['tid']) && hash_equals((string) $tenantId, (string) $claims['tid']);
    }

    private function decodeJwtPayload(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) < 2) {
            return [];
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return [];
        }

        return json_decode($decoded, true) ?: [];
    }

    private function emailBelongsToAllowedDomain(string $email): bool
    {
        $allowedDomains = array_map(
            fn($domain) => strtolower(trim((string) $domain)),
            config('services.microsoft.allowed_domains', [])
        );

        if (empty($allowedDomains)) {
            return true;
        }

        $domain = strtolower(Str::afterLast($email, '@'));

        return $domain !== '' && in_array($domain, $allowedDomains, true);
    }

    private function defaultProvisionRole(): string
    {
        $role = strtolower(trim((string) config('services.microsoft.default_role', 'operador')));

        return in_array($role, ['operador'], true) ? $role : 'operador';
    }

    private function insertMicrosoftUserLog(object $user, string $acao, array $dados, Request $request): void
    {
        Log::info($acao, array_merge($dados, [
            'usuario_id' => $user->id_user ?? null,
            'ip' => $request->ip(),
        ]));

        if (empty($user->id_user) || empty($user->unidade_id)) {
            return;
        }

        try {
            DB::table('_tb_user_logs')->insert([
                'usuario_id' => $user->id_user,
                'unidade_id' => $user->unidade_id,
                'acao' => $acao,
                'dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                'navegador' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('login_microsoft_api - falha_ao_gravar_log_usuario', [
                'erro' => $e->getMessage(),
                'usuario_id' => $user->id_user ?? null,
            ]);
        }
    }
}
