<?php

namespace Tests\Feature;

use App\Models\DispositivoAutorizado;
use App\Services\DeviceAuthorizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginDeviceIdTest extends TestCase
{
    public function test_login_generates_wms_device_cookie_when_missing(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertPlainCookie(DeviceAuthorizationService::COOKIE_NAME);
        $response->assertSee('ID deste dispositivo');
    }

    public function test_login_displays_existing_wms_device_id(): void
    {
        $deviceId = 'web-device-test-123';

        $response = $this
            ->withUnencryptedCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId)
            ->get('/login');

        $response->assertOk();
        $response->assertSee($deviceId);
        $response->assertPlainCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);
    }

    public function test_login_uses_legacy_device_cookie_when_new_cookie_is_missing(): void
    {
        $deviceId = 'web-device-legacy-123';

        $response = $this
            ->withUnencryptedCookie(DeviceAuthorizationService::LEGACY_COOKIE_NAME, $deviceId)
            ->get('/login');

        $response->assertOk();
        $response->assertSee($deviceId);
        $response->assertPlainCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);
    }

    public function test_successful_login_preserves_browser_device_cookie(): void
    {
        [$email, $password] = $this->createWebUser();
        $deviceId = 'web-device-login-123';

        $response = $this
            ->withUnencryptedCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId)
            ->post('/login', [
                'email' => $email,
                'password' => $password,
            ]);

        $response->assertRedirect(route('painel.operador'));
        $response->assertPlainCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);
    }

    public function test_login_hides_device_id_when_web_device_is_already_registered(): void
    {
        $deviceId = 'web-device-already-registered';

        DispositivoAutorizado::query()->where('device_id', $deviceId)->delete();

        DispositivoAutorizado::create([
            'nome_dispositivo' => 'Navegador liberado',
            'device_id' => $deviceId,
            'tipo' => 'web',
            'ativo' => true,
        ]);

        $response = $this
            ->withUnencryptedCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId)
            ->get('/login');

        $response->assertOk();
        $response->assertDontSee('ID deste dispositivo');
        $response->assertDontSee($deviceId);
        $response->assertPlainCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);

        DispositivoAutorizado::query()->where('device_id', $deviceId)->delete();
    }

    private function createWebUser(): array
    {
        $unidadeId = DB::table('_tb_unidades')->min('id');

        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->insertGetId([
                'nome' => 'Unidade Teste Login',
                'status' => 'ativo',
                'created_at' => now(),
            ]);
        }

        $email = 'login.device.' . uniqid() . '@example.com';
        $password = 'Secret123!';

        DB::table('_tb_usuarios')->insert([
            'nome' => 'Usuario Teste Login',
            'email' => $email,
            'password' => Hash::make($password),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$email, $password];
    }
}
