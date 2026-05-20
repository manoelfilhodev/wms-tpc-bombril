<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
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
}
