<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
use App\Services\DeviceAuthorizationService;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemLogAuditTest extends TestCase
{
    public function test_service_registra_log_com_campos_nulos_e_mascara_dados_sensiveis(): void
    {
        SystemLogService::record([
            'module' => 'sistema',
            'action' => 'teste_resiliencia',
            'description' => 'Teste de log com campos nulos.',
            'new_values' => [
                'email' => 'usuario@example.com',
                'password' => 'Secret123!',
                'token_api' => 'abc',
            ],
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'sistema',
            'action' => 'teste_resiliencia',
            'user_id' => null,
        ]);

        $log = DB::table('_tb_system_logs')->where('action', 'teste_resiliencia')->first();
        $dados = json_decode($log->new_values, true);

        $this->assertSame('[REDACTED]', $dados['password']);
        $this->assertSame('[REDACTED]', $dados['token_api']);
    }

    public function test_login_realizado_gera_log_de_auditoria(): void
    {
        $user = $this->createUser('admin');

        $this->withUnencryptedCookie(DeviceAuthorizationService::COOKIE_NAME, 'device-web-1')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'Secret123!',
            ])
            ->assertRedirect(route('demandas.dashboardOperacional'));

        $this->assertDatabaseHas('_tb_system_logs', [
            'user_id' => $user->id_user,
            'module' => 'login',
            'action' => 'login_realizado',
            'device_id' => 'device-web-1',
        ]);
    }

    public function test_importacao_explosao_gera_log(): void
    {
        $this->actingAsAdmin();

        $planilha = "Transporte\tMaterial\tSobra\nDT-AUDIT-IMP-001\t000123\t5";

        $this->post(route('demandas.import'), ['planilha' => $planilha])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'importacao',
            'action' => 'importacao_explosao_realizada',
        ]);
    }

    public function test_separacao_gera_logs_de_inicio_e_finalizacao(): void
    {
        $this->actingAsAdmin();

        $demanda = Demanda::create($this->demandaData('DT-AUDIT-SEP-001', [
            'possui_sobra' => true,
        ]));

        $this->post(route('demandas.iniciarSeparacao', $demanda->id))
            ->assertSessionHas('success');

        $this->post(route('demandas.finalizarSeparacao', $demanda->id), [
            'resultado' => 'COMPLETA',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'separacao',
            'action' => 'separacao_iniciada',
            'entity_id' => (string) $demanda->id,
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'separacao',
            'action' => 'separacao_finalizada',
            'entity_id' => (string) $demanda->id,
        ]);
    }

    public function test_lancamento_operacional_de_item_de_separacao_gera_log(): void
    {
        $admin = $this->actingAsAdmin();

        $materialId = DB::table('_tb_materiais')->insertGetId([
            'unidade_id' => $admin->unidade_id,
            'sku' => 'SKU-AUDIT-001',
            'nome' => 'SKU Auditoria',
            'descricao' => 'SKU Auditoria',
            'created_at' => now(),
        ]);

        $posicaoId = DB::table('_tb_posicoes')->insertGetId([
            'codigo_posicao' => 'AUD-001',
            'unidade_id' => $admin->unidade_id,
            'status' => 'ativa',
            'created_at' => now(),
        ]);

        DB::table('_tb_saldo_estoque')->insert([
            'sku_id' => $materialId,
            'posicao_id' => $posicaoId,
            'quantidade' => 10,
            'unidade_id' => $admin->unidade_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pedidoId = DB::table('_tb_pedidos')->insertGetId([
            'numero_pedido' => 'PED-AUDIT-001',
            'unidade_id' => $admin->unidade_id,
            'status' => 'em_separacao',
            'criado_por' => $admin->id_user,
            'data_criacao' => now(),
        ]);

        $itemId = DB::table('_tb_separacao_itens')->insertGetId([
            'pedido_id' => $pedidoId,
            'sku' => 'SKU-AUDIT-001',
            'quantidade' => 3,
            'fo' => 'FO-AUDIT-001',
            'usuario_id' => $admin->id_user,
            'unidade_id' => $admin->unidade_id,
            'conferido' => false,
            'status' => 'ABERTA',
            'created_at' => now(),
        ]);

        $this->post(route('separacoes.executar.legacy_id', $itemId), [
            'quantidade_separada' => 3,
            'observacoes' => 'Teste de auditoria',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'separacao',
            'action' => 'lancamento_item_realizado',
            'entity_type' => 'separacao_item',
            'entity_id' => (string) $itemId,
        ]);
    }

    public function test_expedicao_gera_log_de_apontamento_operacional(): void
    {
        $this->actingAsAdmin();

        $demanda = Demanda::create($this->demandaData('DT-AUDIT-EXP-001'));

        $this->post(route('expedicao.programacoes.apontamento-operacional.store', $demanda->fo), [
            'etapa' => 'conferencia',
            'acao' => 'iniciar_agora',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'expedicao',
            'action' => 'apontamento_operacional_salvo',
            'entity_id' => (string) $demanda->id,
        ]);
    }

    public function test_admin_consegue_filtrar_logs_e_usuario_comum_e_bloqueado(): void
    {
        $admin = $this->createUser('admin');
        $operador = $this->createUser('operador');

        SystemLogService::record([
            'user_id' => $admin->id_user,
            'user_name' => $admin->nome,
            'user_email' => $admin->email,
            'module' => 'separacao',
            'action' => 'acao_filtravel',
            'description' => 'Log filtrável para auditoria.',
        ]);

        $this->actingAs($admin)
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin'])
            ->get(route('admin.logs.index', ['module' => 'separacao', 'q' => 'filtrável']))
            ->assertOk()
            ->assertSee('acao_filtravel')
            ->assertSee('Registros de auditoria');

        $this->actingAs($operador)
            ->withSession(['tipo' => 'operador', 'nivel' => 'Operador'])
            ->get(route('admin.logs.index'))
            ->assertRedirect(route('dashboard'));
    }

    private function actingAsAdmin(): User
    {
        $user = $this->createUser('admin');
        $this->actingAs($user)->withSession([
            'tipo' => 'admin',
            'nivel' => 'Admin',
            'user_id' => $user->id_user,
            'nome' => $user->nome,
            'unidade' => $user->unidade_id,
        ]);

        return $user;
    }

    private function createUser(string $tipo): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Auditoria ' . uniqid(),
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Auditoria ' . uniqid(),
            'email' => 'auditoria.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => ucfirst($tipo),
        ]);
    }

    private function demandaData(string $fo, array $extra = []): array
    {
        return array_merge([
            'fo' => $fo,
            'cliente' => 'Cliente Auditoria',
            'transportadora' => 'Transportadora Auditoria',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
            'quantidade' => 1,
            'possui_sobra' => false,
        ], $extra);
    }
}
