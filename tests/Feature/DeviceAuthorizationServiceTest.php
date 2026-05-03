<?php

namespace Tests\Feature;

use App\Models\DispositivoAutorizado;
use App\Models\User;
use App\Services\DeviceAuthorizationService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeviceAuthorizationServiceTest extends TestCase
{
    public function test_operador_requires_authorized_web_device(): void
    {
        $service = app(DeviceAuthorizationService::class);
        $operador = $this->createUser('operador');

        $this->assertTrue($service->requiresDeviceValidation($operador));
        $this->assertNull($service->findAuthorizedDevice($operador, 'device-web-1', 'web'));

        DispositivoAutorizado::create([
            'nome_dispositivo' => 'Coletor Web 1',
            'device_id' => 'device-web-1',
            'tipo' => 'web',
            'ativo' => true,
        ]);

        $this->assertNotNull($service->findAuthorizedDevice($operador, 'device-web-1', 'web'));
    }

    public function test_admin_does_not_require_device_validation(): void
    {
        $service = app(DeviceAuthorizationService::class);
        $admin = $this->createUser('admin');

        $this->assertFalse($service->requiresDeviceValidation($admin));
    }

    private function createUser(string $tipo): User
    {
        DB::table('_tb_unidades')->insertOrIgnore([
            'id' => 1,
            'nome' => 'Unidade Central',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => "Usuario {$tipo}",
            'email' => "{$tipo}@systex.com.br",
            'password' => bcrypt('secret'),
            'unidade_id' => 1,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => ucfirst($tipo),
        ]);
    }
}
