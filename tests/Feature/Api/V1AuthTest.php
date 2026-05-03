<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V1AuthTest extends TestCase
{
    public function test_v1_root_requires_authentication(): void
    {
        $this->getJson('/api/v1')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_api_login_returns_token(): void
    {
        [$email, $password] = $this->createApiUser();

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'nome', 'tipo', 'unidade', 'nivel'],
            ]);
    }

    public function test_v1_me_returns_authenticated_user_with_token(): void
    {
        [$email] = $this->createApiUser();

        $user = User::query()->where('email', $email)->firstOrFail();
        $token = $user->createToken('test-v1-me')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id_user)
            ->assertJsonPath('data.email', $user->email);
    }

    private function createApiUser(): array
    {
        $unidadeId = DB::table('_tb_unidades')->min('id');

        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->insertGetId([
                'nome' => 'Unidade Teste API',
                'status' => 'ativo',
                'created_at' => now(),
            ]);
        }

        $email = 'api.test.' . uniqid() . '@example.com';
        $password = 'Secret123!';

        DB::table('_tb_usuarios')->insert([
            'nome' => 'Usuario Teste API',
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
