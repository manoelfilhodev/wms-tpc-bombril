<?php

namespace Tests\Feature;

use App\Enums\AuditSeverity;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Services\SecurityMonitorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityOperationsTest extends TestCase
{
    public function test_request_correlation_headers_are_returned(): void
    {
        $correlationId = (string) Str::uuid();

        $this->get('/login', ['X-Correlation-Id' => $correlationId])
            ->assertOk()
            ->assertHeader('X-Correlation-Id', $correlationId)
            ->assertHeader('X-Request-Id');
    }

    public function test_failed_login_audit_includes_correlation_metadata(): void
    {
        RateLimiter::clear('missing.securityops@example.com|127.0.0.1');

        $correlationId = (string) Str::uuid();

        $this->from('/login')->post('/login', [
            'email' => 'missing.securityops@example.com',
            'password' => 'wrong-password',
        ], ['X-Correlation-Id' => $correlationId])->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_FAILURE',
            'module' => 'login',
            'severity' => AuditSeverity::WARNING->value,
            'correlation_id' => $correlationId,
            'response_status' => null,
        ]);
    }

    public function test_csp_uses_nonce_and_blocks_unsafe_eval(): void
    {
        $response = $this->get('/login')->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
    }

    public function test_security_monitor_creates_alert_for_login_failure_spike(): void
    {
        for ($i = 0; $i < 9; $i++) {
            AuditLog::create([
                'action' => 'LOGIN_FAILURE',
                'module' => 'login',
                'severity' => AuditSeverity::WARNING,
                'ip' => '10.10.10.10',
                'created_at' => now(),
            ]);
        }

        $log = AuditLog::create([
            'action' => 'LOGIN_FAILURE',
            'module' => 'login',
            'severity' => AuditSeverity::WARNING,
            'ip' => '10.10.10.10',
            'correlation_id' => (string) Str::uuid(),
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
        ]);

        app(SecurityMonitorService::class)->inspect($log);

        $this->assertDatabaseHas('security_alerts', [
            'type' => 'LOGIN_FAILURE_SPIKE',
            'severity' => AuditSeverity::CRITICAL->value,
        ]);
    }

    public function test_security_dashboard_requires_admin_permission(): void
    {
        $operator = $this->createSecurityUser('operador', 'Operador');

        $this->actingAs($operator)->withSecuritySession($operator);
        $this->get(route('admin.security.index'))->assertForbidden();

        $admin = $this->createSecurityUser('admin', 'Administrador');

        SecurityAlert::create([
            'type' => 'LOGIN_FAILURE_SPIKE',
            'severity' => AuditSeverity::CRITICAL,
            'title' => 'Muitas falhas de login',
            'description' => 'Teste automatizado',
            'context' => ['ip' => '127.0.0.1'],
        ]);

        $this->actingAs($admin)->withSecuritySession($admin);
        $this->get(route('admin.security.index'))
            ->assertOk()
            ->assertSee('Security Operations');
    }

    public function test_api_unauthenticated_response_includes_correlation_metadata(): void
    {
        $this->getJson('/api/demandas')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'meta' => [
                    'correlation_id',
                    'request_id',
                ],
            ]);
    }

    public function test_logout_is_post_only_and_uses_web_csrf_surface(): void
    {
        $route = app('router')->getRoutes()->getByName('logout');

        $this->assertNotNull($route);
        $this->assertSame(['POST'], $route->methods());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('throttle:critical', $route->gatherMiddleware());
        $this->get('/logout')->assertMethodNotAllowed();
    }

    private function createSecurityUser(string $tipo = 'operador', string $nivel = 'Operador'): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Security Operations',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Security Operations',
            'email' => 'security.operations.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => $nivel,
        ]);
    }

    private function withSecuritySession(User $user): self
    {
        $this->withSession([
            'tipo' => $user->tipo,
            'nivel' => $user->nivel,
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        return $this;
    }
}
