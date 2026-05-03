<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApontamentoPaleteStretchApiTest extends TestCase
{
    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/apontamentos-paletes-stretch', [])
            ->assertStatus(401);
    }

    public function test_store_creates_stretch_appointment(): void
    {
        $user = $this->createApiUser();
        $token = $user->createToken('test-stretch')->plainTextToken;
        $uuid = (string) Str::uuid();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/apontamentos-paletes-stretch', [
                'palete_codigo' => ' pal-001 ',
                'origem' => 'APP',
                'device_id' => 'coletor-01',
                'app_version' => '1.0.0',
                'client_uuid' => $uuid,
                'apontado_em_app' => '2026-05-02T10:30:00-03:00',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.palete_codigo', 'PAL-001')
            ->assertJsonPath('data.origem', 'APP')
            ->assertJsonPath('data.usuario_id', $user->id_user)
            ->assertJsonPath('data.unidade_id', $user->unidade_id)
            ->assertJsonPath('data.client_uuid', $uuid);

        $this->assertDatabaseHas('_tb_apontamentos_paletes_stretch', [
            'palete_codigo' => 'PAL-001',
            'client_uuid' => $uuid,
            'origem' => 'APP',
            'status' => 'APONTADO',
        ]);
    }

    public function test_store_is_idempotent_by_client_uuid(): void
    {
        $user = $this->createApiUser();
        $token = $user->createToken('test-stretch-idempotent')->plainTextToken;
        $uuid = (string) Str::uuid();

        $payload = [
            'palete_codigo' => 'PAL-002',
            'client_uuid' => $uuid,
        ];

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/apontamentos-paletes-stretch', $payload)
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/apontamentos-paletes-stretch', $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.idempotent', true);

        $this->assertSame(1, DB::table('_tb_apontamentos_paletes_stretch')->where('client_uuid', $uuid)->count());
    }

    public function test_store_blocks_duplicate_active_pallet(): void
    {
        $user = $this->createApiUser();
        $token = $user->createToken('test-stretch-duplicate')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/apontamentos-paletes-stretch', [
                'palete_codigo' => 'PAL-003',
                'client_uuid' => (string) Str::uuid(),
            ])
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/apontamentos-paletes-stretch', [
                'palete_codigo' => 'PAL-003',
                'client_uuid' => (string) Str::uuid(),
            ])
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    private function createApiUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->min('id');

        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->insertGetId([
                'nome' => 'Unidade Teste API',
                'status' => 'ativo',
                'created_at' => now(),
            ]);
        }

        $id = DB::table('_tb_usuarios')->insertGetId([
            'nome' => 'Usuario Teste API',
            'email' => 'stretch.api.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($id);
    }
}
