<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityPhase2Test extends TestCase
{
    public function test_rbac_permissions_are_seeded_by_migration(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'demandas.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.access']);
        $this->assertDatabaseHas('roles', ['name' => 'operador']);
    }

    public function test_permission_middleware_allows_user_with_database_permission(): void
    {
        $user = $this->createSecurityUser();

        Sanctum::actingAs($user);

        $this->getJson('/api/demandas')
            ->assertOk();
    }

    public function test_permission_middleware_denies_user_without_database_permission_and_audits(): void
    {
        $user = $this->createSecurityUser();
        $user->roles()->detach();

        Sanctum::actingAs($user);

        $this->getJson('/api/demandas')
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id_user,
            'action' => 'PERMISSION_DENIED',
            'module' => 'authorization',
        ]);
    }

    public function test_security_headers_are_applied(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_login_rate_limit_is_enforced_and_audited(): void
    {
        RateLimiter::clear('phase2@example.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'phase2@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect('/login');
        }

        $this->from('/login')->post('/login', [
            'email' => 'phase2@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'RATE_LIMIT_TRIGGERED',
            'module' => 'login',
        ]);
    }

    public function test_login_does_not_reveal_user_enumeration(): void
    {
        $user = $this->createSecurityUser();

        $missing = $this->from('/login')->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ]);

        $wrongPassword = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $missing->assertSessionHas('error', 'Credenciais invalidas.');
        $wrongPassword->assertSessionHas('error', 'Credenciais invalidas.');
    }

    public function test_malicious_upload_extension_is_blocked(): void
    {
        $user = $this->createSecurityUser();

        $this->actingAs($user)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        $file = UploadedFile::fake()->create('shell.php', 1, 'application/x-php');

        $this->post(route('setores.recebimento.parseXml'), [
            'xml' => $file,
        ])->assertStatus(422);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'blocked_malicious_upload',
            'module' => 'uploads',
        ]);
    }

    private function createSecurityUser(string $tipo = 'operador', string $nivel = 'Operador'): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Security Phase 2',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Security Phase 2',
            'email' => 'security.phase2.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => $nivel,
        ]);
    }
}
