<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StretchIdentificacaoAuditTest extends TestCase
{
    public function test_apontamento_web_de_palete_stretch_gera_log(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->post(route('stretch.apontar.store'), [
            'palete_codigo' => 'PAL-STRETCH-AUDIT-001',
            'observacao' => 'Teste auditoria',
        ])->assertSessionHas('success');

        $apontamentoId = DB::table('_tb_apontamentos_paletes_stretch')
            ->where('palete_codigo', 'PAL-STRETCH-AUDIT-001')
            ->value('id');

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'stretch',
            'action' => 'palete_stretch_apontado',
            'entity_type' => 'apontamento_palete_stretch',
            'entity_id' => (string) $apontamentoId,
        ]);
    }

    public function test_geracao_e_impressao_de_identificacao_a4_geram_logs(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $response = $this->get(route('demandas.identificacaoA4', [
            'tipo' => 'dt',
            'dt' => 'DT-PRINT-001',
            'pallets' => '12',
            'data' => '2026-05-19',
            'conferente' => 'Maria',
        ]))->assertOk();

        $html = $response->getContent();
        $this->assertSame(2, substr_count($html, 'class="id-copy"'));
        $this->assertStringContainsString('height: 148.5mm;', $html);
        $this->assertStringContainsString('box-sizing: border-box', $html);

        $this->post(route('demandas.identificacaoA4.auditPrint'), [
            'tipo' => 'dt',
            'dt' => 'DT-PRINT-001',
            'pallets' => '12',
            'data' => '2026-05-19',
            'conferente' => 'MARIA',
        ])->assertNoContent();

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'separacao',
            'action' => 'identificacao_a4_gerada',
            'entity_type' => 'demanda',
            'entity_id' => 'DT-PRINT-001',
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'separacao',
            'action' => 'identificacao_a4_impressa',
            'entity_type' => 'demanda',
            'entity_id' => 'DT-PRINT-001',
        ]);
    }

    private function createOperator(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Stretch Auditoria',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Operador Stretch Auditoria',
            'email' => 'operador.stretch.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
        ]);
    }
}
