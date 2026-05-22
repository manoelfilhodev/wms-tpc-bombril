<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\Expedicao\ExpedicaoProgramacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaReportGerencialEtapasTest extends TestCase
{
    public function test_report_gerencial_diferencia_separacao_de_expedicao(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        Demanda::create([
            'fo' => 'DT-SEP-SEM-EXP-001',
            'cliente' => 'Cliente Etapas',
            'transportadora' => 'Transportadora Etapas',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'quantidade' => 10,
            'possui_sobra' => true,
            'separacao_iniciada_em' => '2026-05-20 08:00:00',
            'separacao_finalizada_em' => '2026-05-20 09:00:00',
            'separacao_resultado' => 'COMPLETA',
            'carregamento_finalizado_em' => null,
            'created_at' => '2026-05-20 07:00:00',
        ]);

        ExpedicaoProgramacao::create([
            'fo' => 'DT-SEP-SEM-EXP-001',
            'dt_sap' => 'DT-SEP-SEM-EXP-001',
            'agenda_entrega_em' => '2026-05-20 12:00:00',
            'cidade_destino' => 'SAO PAULO',
            'uf_destino' => 'SP',
            'tipo_demanda' => ExpedicaoProgramacao::TIPO_PROGRAMADA,
            'origem_demanda' => ExpedicaoProgramacao::ORIGEM_PLANILHA_MANHA,
            'status_previsao' => 'AGUARDANDO_EXPLOSAO',
        ]);

        $this->get(route('demandas.reportGerencial', [
            'data_inicio' => '2026-05-20',
            'data_fim' => '2026-05-20',
        ]))
            ->assertOk()
            ->assertSee('Separação finalizada')
            ->assertSee('Expedição finalizada')
            ->assertSee('separadas aguardando expedição')
            ->assertSee('Atendimento separação')
            ->assertSee('Atendimento expedição');
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Report Gerencial',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Report Gerencial',
            'email' => 'report.gerencial.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
