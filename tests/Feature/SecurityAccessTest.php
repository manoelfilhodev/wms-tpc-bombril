<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    public function test_sensitive_web_route_redirects_guest_to_login(): void
    {
        $this->get('/setores/recebimento/painel')
            ->assertRedirect('/login');
    }

    public function test_sensitive_web_route_allows_authenticated_user(): void
    {
        $user = $this->createSecurityUser();

        $this->actingAs($user)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        $this->get('/painel-operador')->assertOk();
    }

    public function test_internal_api_demands_requires_token(): void
    {
        $this->getJson('/api/demandas')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_logout_only_accepts_post(): void
    {
        $user = $this->createSecurityUser();

        $this->actingAs($user)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        $this->get('/logout')->assertStatus(405);

        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_public_register_is_blocked_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/register')
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Cadastro publico desabilitado.');
    }

    public function test_user_without_admin_permission_receives_403(): void
    {
        $user = $this->createSecurityUser('operador', 'Operador');

        $this->actingAs($user)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        $this->get('/usuarios')->assertForbidden();
    }

    private function createSecurityUser(string $tipo = 'operador', string $nivel = 'Operador'): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Seguranca',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Seguranca',
            'email' => 'seguranca.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => $nivel,
        ]);
    }
}
