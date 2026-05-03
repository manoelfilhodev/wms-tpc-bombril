<?php

namespace Tests\Feature;

use App\Models\DispositivoAutorizado;
use App\Services\DeviceAuthorizationService;
use Tests\TestCase;

class LoginDeviceIdTest extends TestCase
{
    public function test_login_generates_wms_device_cookie_when_missing(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertCookie(DeviceAuthorizationService::COOKIE_NAME);
        $response->assertSee('ID deste dispositivo');
    }

    public function test_login_displays_existing_wms_device_id(): void
    {
        $deviceId = 'web-device-test-123';

        $response = $this
            ->withCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId)
            ->get('/login');

        $response->assertOk();
        $response->assertSee($deviceId);
        $response->assertCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);
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
            ->withCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId)
            ->get('/login');

        $response->assertOk();
        $response->assertDontSee('ID deste dispositivo');
        $response->assertDontSee($deviceId);
        $response->assertCookie(DeviceAuthorizationService::COOKIE_NAME, $deviceId);

        DispositivoAutorizado::query()->where('device_id', $deviceId)->delete();
    }
}
