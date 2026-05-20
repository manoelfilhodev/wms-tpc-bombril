<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaOperadorAcessoOperacionalTest extends TestCase
{
    public function test_operador_pode_acessar_importacao_e_identificacao_operacional(): void
    {
        $operador = $this->createOperator();

        $this->actingAs($operador)->withSession([
            'tipo' => 'operador',
            'nivel' => 'Operador',
            'user_id' => $operador->id_user,
            'nome' => $operador->nome,
            'unidade' => $operador->unidade_id,
        ]);

        $this->get(route('demandas.import.view'))
            ->assertOk()
            ->assertSee('Importar DTs SAP');

        $this->get(route('demandas.identificacaoA4'))
            ->assertOk()
            ->assertSee('Identificação A4');

        $planilha = "Transporte\tMaterial\tSobra\nDT-OPERADOR-IMPORT-001\t000123\t5";

        $this->post(route('demandas.import'), ['planilha' => $planilha])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => 'DT-OPERADOR-IMPORT-001',
        ]);
    }

    private function createOperator(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Operador Acesso',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Operador Acesso',
            'email' => 'operador.acesso.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'operador',
            'status' => 'ativo',
            'nivel' => 'Operador',
        ]);
    }
}
