<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpedicaoOperadorAcessoTest extends TestCase
{
    public function test_operador_pode_acessar_e_apontar_expedicao_operacional(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $demanda = Demanda::create([
            'fo' => 'FO-EXP-OPERADOR-001',
            'cliente' => 'Cliente Expedição',
            'transportadora' => 'Transportadora Expedição',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
        ]);

        $this->get(route('expedicao.apontamentos-operacionais.index'))
            ->assertOk()
            ->assertSee('Apontamentos Operacionais');

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

    public function test_inicio_de_etapa_nao_sobrescreve_horario_e_edicao_manual_gera_log(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $demanda = Demanda::create([
            'fo' => 'FO-EXP-LOCK-001',
            'cliente' => 'Cliente Expedição',
            'transportadora' => 'Transportadora Expedição',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
        ]);

        Carbon::setTestNow('2026-05-20 08:00:00');

        $this->post(route('expedicao.programacoes.apontamento-operacional.store', $demanda->fo), [
            'etapa' => 'conferencia',
            'acao' => 'iniciar_agora',
        ])->assertSessionHas('success');

        Carbon::setTestNow('2026-05-20 09:00:00');

        $this->post(route('expedicao.programacoes.apontamento-operacional.store', $demanda->fo), [
            'etapa' => 'conferencia',
            'acao' => 'iniciar_agora',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => $demanda->fo,
            'conferencia_iniciada_em' => '2026-05-20 08:00:00',
        ]);

        $this->post(route('expedicao.programacoes.apontamento-operacional.store', $demanda->fo), [
            'etapa' => 'conferencia',
            'acao' => 'salvar_manual',
            'inicio' => '2026-05-20 08:30:00',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => $demanda->fo,
            'conferencia_iniciada_em' => '2026-05-20 08:30:00',
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'expedicao',
            'action' => 'apontamento_operacional_salvo',
            'entity_id' => (string) $demanda->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_apontamento_exibe_apenas_dts_com_separacao_finalizada_e_sem_expedicao_finalizada(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-FILA-001', [
            'separacao_finalizada_em' => now()->subHour(),
            'conferencia_finalizada_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-FILA-002', [
            'separacao_finalizada_em' => null,
            'conferencia_finalizada_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-FILA-003', [
            'separacao_finalizada_em' => now()->subHours(2),
            'conferencia_finalizada_em' => now()->subHour(),
            'carregamento_finalizado_em' => now()->subMinutes(10),
        ]);

        $this->get(route('expedicao.apontamentos-operacionais.index'))
            ->assertOk()
            ->assertSee('FO-EXP-FILA-001')
            ->assertDontSee('FO-EXP-FILA-002')
            ->assertDontSee('FO-EXP-FILA-003');
    }

    public function test_filtros_da_expedicao_aceitam_varias_dts_no_mesmo_campo(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-MULTI-001', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_finalizada_em' => now()->subHours(3),
            'carregamento_finalizado_em' => now()->subHour(),
            'saida_veiculo_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-MULTI-002', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_finalizada_em' => now()->subHours(3),
            'carregamento_finalizado_em' => now()->subHour(),
            'saida_veiculo_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-MULTI-003', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_finalizada_em' => now()->subHours(3),
            'carregamento_finalizado_em' => now()->subHour(),
            'saida_veiculo_em' => null,
        ]);

        $busca = 'FO-EXP-MULTI-001, FO-EXP-MULTI-002';

        $this->get(route('expedicao.apontamentos-operacionais.index', ['busca' => $busca]))
            ->assertOk()
            ->assertSee('FO-EXP-MULTI-001')
            ->assertSee('FO-EXP-MULTI-002')
            ->assertDontSee('FO-EXP-MULTI-003');

        $this->get(route('expedicao.saida-veiculos.index', ['busca' => $busca]))
            ->assertOk()
            ->assertSee('FO-EXP-MULTI-001')
            ->assertSee('FO-EXP-MULTI-002')
            ->assertDontSee('FO-EXP-MULTI-003');

        $this->get(route('expedicao.timeline-dts.index', ['busca' => $busca]))
            ->assertOk()
            ->assertSee('FO-EXP-MULTI-001')
            ->assertSee('FO-EXP-MULTI-002')
            ->assertDontSee('FO-EXP-MULTI-003');
    }

    public function test_apontamento_exibe_dt_separada_sem_programacao_como_oportunidade(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        Demanda::create([
            'fo' => 'FO-OPORT-SEM-PROG-001',
            'cliente' => 'Cliente Oportunidade',
            'transportadora' => 'Transportadora Oportunidade',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
            'separacao_finalizada_em' => now()->subHour(),
            'conferencia_finalizada_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->get(route('expedicao.apontamentos-operacionais.index', ['tipo_demanda' => 'OPORTUNIDADE']))
            ->assertOk()
            ->assertSee('FO-OPORT-SEM-PROG-001')
            ->assertSee('Oportunidade');

        $this->get(route('expedicao.apontamentos-operacionais.index', ['tipo_demanda' => 'PROGRAMADA']))
            ->assertOk()
            ->assertDontSee('FO-OPORT-SEM-PROG-001');
    }

    public function test_previsibilidade_nao_exibe_dts_com_expedicao_finalizada(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-PREV-ATIVA-001', [
            'separacao_finalizada_em' => now()->subHour(),
            'conferencia_finalizada_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-PREV-FINAL-001', [
            'separacao_finalizada_em' => now()->subHours(2),
            'conferencia_finalizada_em' => now()->subHour(),
            'carregamento_finalizado_em' => now()->subMinutes(10),
        ]);

        $this->criarProgramacaoComDemanda('FO-PREV-SAIDA-001', [
            'separacao_finalizada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_finalizado_em' => now()->subHour(),
            'saida_veiculo_em' => now()->subMinutes(20),
        ]);

        $this->criarProgramacaoComDemanda('FO-PREV-SAIDA-ONTEM-001', [
            'separacao_finalizada_em' => now()->subDays(2),
            'conferencia_finalizada_em' => now()->subDays(2)->addHour(),
            'carregamento_finalizado_em' => now()->subDay()->subHour(),
            'saida_veiculo_em' => now()->subDay(),
        ]);

        $this->get(route('expedicao.previsibilidade.index'))
            ->assertOk()
            ->assertSee('FO-PREV-ATIVA-001')
            ->assertSee('DTs finalizadas')
            ->assertSee('Com saída de veículo')
            ->assertSee('FO-PREV-FINAL-001')
            ->assertSee('FO-PREV-SAIDA-001')
            ->assertDontSee('FO-PREV-SAIDA-ONTEM-001')
            ->assertSee('aguardando saída')
            ->assertSee('ciclo fechado');
    }

    public function test_timeline_geral_consulta_dts_finalizadas_e_nao_finalizadas(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-TIMELINE-ABERTA-001', [
            'separacao_iniciada_em' => now()->subHour(),
            'separacao_finalizada_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-TIMELINE-SAIDA-001', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_finalizada_em' => now()->subHours(3),
            'carregamento_finalizado_em' => now()->subHours(2),
            'saida_veiculo_em' => now()->subHour(),
        ]);

        $this->get(route('expedicao.timeline-dts.index', ['busca' => 'FO-TIMELINE']))
            ->assertOk()
            ->assertSee('Timeline das DTs')
            ->assertSee('FO-TIMELINE-ABERTA-001')
            ->assertSee('FO-TIMELINE-SAIDA-001')
            ->assertSee('Ver timeline');

        $this->get(route('expedicao.timeline-dts.show', 'FO-TIMELINE-ABERTA-001'))
            ->assertOk()
            ->assertSee('Timeline Geral da DT')
            ->assertSee('Início da separação')
            ->assertSee('Fim da separação')
            ->assertSee('Pendente');
    }

    public function test_resumo_da_expedicao_conta_estagios_operacionais(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-RES-001', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => null,
            'conferencia_finalizada_em' => null,
            'carregamento_iniciado_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-RES-002', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => null,
            'carregamento_iniciado_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-RES-003', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_iniciado_em' => null,
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-RES-004', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_iniciado_em' => now()->subHour(),
            'carregamento_finalizado_em' => null,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-RES-005', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_iniciado_em' => now()->subHour(),
            'carregamento_finalizado_em' => now()->subMinutes(10),
        ]);

        $this->get(route('expedicao.apontamentos-operacionais.index', ['busca' => 'FO-EXP-RES']))
            ->assertOk()
            ->assertSee('Aguard. conf.')
            ->assertSee('Conferindo')
            ->assertSee('Aguard. carga')
            ->assertSee('Carregando')
            ->assertSee('Finalizadas');
    }

    public function test_carregamento_nao_inicia_sem_conferencia_finalizada(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $demanda = Demanda::create([
            'fo' => 'FO-EXP-BLOCK-CARGA-001',
            'cliente' => 'Cliente Expedição',
            'transportadora' => 'Transportadora Expedição',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
            'conferencia_iniciada_em' => now()->subHour(),
            'conferencia_finalizada_em' => null,
        ]);

        $this->post(route('expedicao.programacoes.apontamento-operacional.store', $demanda->fo), [
            'etapa' => 'carregamento',
            'acao' => 'iniciar_agora',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => $demanda->fo,
            'carregamento_iniciado_em' => null,
        ]);
    }

    public function test_saida_de_veiculo_fecha_ciclo_e_gera_log(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->criarProgramacaoComDemanda('FO-EXP-SAIDA-001', [
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_iniciada_em' => now()->subHours(3),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_iniciado_em' => now()->subHour(),
            'carregamento_finalizado_em' => now()->subMinutes(10),
            'saida_veiculo_em' => null,
        ]);

        $demanda = Demanda::where('fo', 'FO-EXP-SAIDA-001')->firstOrFail();

        $this->get(route('expedicao.saida-veiculos.index'))
            ->assertOk()
            ->assertSee('FO-EXP-SAIDA-001')
            ->assertSee('Registrar saída');

        $this->post(route('expedicao.programacoes.saida-veiculo.store', 'FO-EXP-SAIDA-001'), [
            'observacao' => 'Motorista liberado',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => 'FO-EXP-SAIDA-001',
            'saida_veiculo_observacao' => 'Motorista liberado',
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'expedicao',
            'action' => 'saida_veiculo_registrada',
            'entity_id' => (string) $demanda->id,
        ]);
    }

    public function test_saida_de_veiculo_exibe_dt_carregada_sem_programacao(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        Demanda::create([
            'fo' => 'FO-SAIDA-SEM-PROG-001',
            'cliente' => 'Cliente Saida Sem Prog',
            'transportadora' => 'Transportadora Saida',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
            'separacao_finalizada_em' => now()->subHours(4),
            'conferencia_finalizada_em' => now()->subHours(2),
            'carregamento_finalizado_em' => now()->subMinutes(30),
            'saida_veiculo_em' => null,
        ]);

        $this->get(route('expedicao.saida-veiculos.index'))
            ->assertOk()
            ->assertSee('FO-SAIDA-SEM-PROG-001')
            ->assertSee('Oportunidade')
            ->assertSee('Registrar saída');

        $this->get(route('expedicao.saida-veiculos.show', 'FO-SAIDA-SEM-PROG-001'))
            ->assertOk()
            ->assertSee('FO-SAIDA-SEM-PROG-001')
            ->assertSee('Oportunidade');
    }

    public function test_timeline_classifica_dt_sem_programacao_como_oportunidade(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        Demanda::create([
            'fo' => 'FO-TL-SEM-PROG-001',
            'cliente' => 'Cliente Timeline Sem Prog',
            'transportadora' => 'Transportadora Timeline',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
            'separacao_finalizada_em' => now()->subHour(),
        ]);

        $this->get(route('expedicao.timeline-dts.index', ['tipo_demanda' => 'OPORTUNIDADE', 'busca' => 'FO-TL-SEM-PROG']))
            ->assertOk()
            ->assertSee('FO-TL-SEM-PROG-001')
            ->assertSee('Oportunidade');

        $this->get(route('expedicao.timeline-dts.index', ['tipo_demanda' => 'PROGRAMADA', 'busca' => 'FO-TL-SEM-PROG']))
            ->assertOk()
            ->assertDontSee('FO-TL-SEM-PROG-001');
    }

    public function test_edicao_de_saida_de_veiculo_e_restrita_a_admin_ou_gestor(): void
    {
        $operador = $this->createOperator();
        $admin = $this->createAdmin();

        $this->criarProgramacaoComDemanda('FO-EXP-SAIDA-EDIT-001', [
            'separacao_finalizada_em' => '2026-05-20 08:00:00',
            'conferencia_iniciada_em' => '2026-05-20 09:00:00',
            'conferencia_finalizada_em' => '2026-05-20 10:00:00',
            'carregamento_iniciado_em' => '2026-05-20 11:00:00',
            'carregamento_finalizado_em' => '2026-05-20 12:00:00',
            'saida_veiculo_em' => '2026-05-20 12:30:00',
        ]);

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->patch(route('expedicao.programacoes.saida-veiculo.update', 'FO-EXP-SAIDA-EDIT-001'), [
            'saida_veiculo_em' => '2026-05-20 12:45:00',
        ])->assertForbidden();

        $this->actingAs($admin)->withSession([
            'tipo' => 'admin',
            'nivel' => 'Admin',
            'user_id' => $admin->id_user,
            'nome' => $admin->nome,
            'unidade' => $admin->unidade_id,
        ]);

        $this->patch(route('expedicao.programacoes.saida-veiculo.update', 'FO-EXP-SAIDA-EDIT-001'), [
            'saida_veiculo_em' => '2026-05-20 12:45:00',
            'observacao' => 'Correção autorizada',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => 'FO-EXP-SAIDA-EDIT-001',
            'saida_veiculo_em' => '2026-05-20 12:45:00',
            'saida_veiculo_observacao' => 'Correção autorizada',
        ]);
    }

    private function createOperator(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Expedição Operador',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Operador Expedição',
            'email' => 'operador.expedicao.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
        ]);
    }

    private function createAdmin(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Expedição Admin',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Admin Expedição',
            'email' => 'admin.expedicao.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }

    private function criarProgramacaoComDemanda(string $fo, array $tempos): void
    {
        DB::table('_tb_demanda')->where('fo', $fo)->delete();
        DB::table('_tb_expedicao_programacoes')->where('fo', $fo)->delete();

        ExpedicaoProgramacao::create([
            'fo' => $fo,
            'dt_sap' => $fo,
            'agenda_entrega_em' => now()->addDay(),
            'cidade_destino' => 'Sao Paulo',
            'uf_destino' => 'SP',
            'cliente' => 'Cliente ' . $fo,
            'transportadora' => 'Transportadora Expedição',
            'tipo_veiculo' => 'TRUCK',
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => true,
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'AGUARDANDO_EXPLOSAO',
        ]);

        Demanda::create(array_merge([
            'fo' => $fo,
            'cliente' => 'Cliente ' . $fo,
            'transportadora' => 'Transportadora Expedição',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 1,
            'possui_sobra' => true,
        ], $tempos));
    }
}
