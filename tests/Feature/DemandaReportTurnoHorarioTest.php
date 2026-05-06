<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\DemandaDistribuicao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaReportTurnoHorarioTest extends TestCase
{
    public function test_report_de_turno_respeita_tolerancia_entre_turno_a_e_b(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $this->createDistribuicaoFinalizada('Ana Turno A', '2026-05-06 14:10:00');
        $this->createDistribuicaoFinalizada('Bruno Turno B', '2026-05-06 14:11:00');

        $this->get(route('demandas.reportTurno', [
            'data' => '2026-05-06',
            'turno' => 'T1',
        ]))
            ->assertOk()
            ->assertSee('ANA TURNO A')
            ->assertDontSee('BRUNO TURNO B');

        $this->get(route('demandas.reportTurno', [
            'data' => '2026-05-06',
            'turno' => 'T2',
        ]))
            ->assertOk()
            ->assertDontSee('ANA TURNO A')
            ->assertSee('BRUNO TURNO B');
    }

    public function test_report_de_turno_respeita_tolerancia_entre_turno_b_e_c(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $this->createDistribuicaoFinalizada('Carla Turno B', '2026-05-06 22:10:00');
        $this->createDistribuicaoFinalizada('Diego Turno C', '2026-05-06 22:11:00');

        $this->get(route('demandas.reportTurno', [
            'data' => '2026-05-06',
            'turno' => 'T2',
        ]))
            ->assertOk()
            ->assertSee('CARLA TURNO B')
            ->assertDontSee('DIEGO TURNO C');

        $this->get(route('demandas.reportTurno', [
            'data' => '2026-05-06',
            'turno' => 'T3',
        ]))
            ->assertOk()
            ->assertDontSee('CARLA TURNO B')
            ->assertSee('DIEGO TURNO C');
    }

    public function test_report_de_turno_c_fecha_as_seis_e_dez_do_dia_seguinte(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $this->createDistribuicaoFinalizada('Eva Turno C', '2026-05-07 06:10:00');
        $this->createDistribuicaoFinalizada('Fabio Turno A', '2026-05-07 06:11:00');

        $this->get(route('demandas.reportTurno', [
            'data' => '2026-05-06',
            'turno' => 'T3',
        ]))
            ->assertOk()
            ->assertSee('EVA TURNO C')
            ->assertDontSee('FABIO TURNO A');
    }

    private function createDistribuicaoFinalizada(string $separador, string $finalizadoEm): void
    {
        $demanda = Demanda::create([
            'fo' => 'DT-TURNO-' . uniqid(),
            'cliente' => 'Cliente Turno',
            'transportadora' => 'Transportadora Turno',
            'tipo' => 'EXPEDICAO',
            'status' => 'CONFERIDO',
            'possui_sobra' => true,
            'stage' => 'BOX 1',
            'separacao_iniciada_em' => date('Y-m-d H:i:s', strtotime($finalizadoEm . ' -1 hour')),
            'separacao_finalizada_em' => $finalizadoEm,
            'separacao_resultado' => 'COMPLETA',
        ]);

        DemandaDistribuicao::create([
            'demanda_id' => $demanda->id,
            'separador_nome' => $separador,
            'quantidade_pecas' => 10,
            'quantidade_skus' => 2,
            'finalizado_em' => $finalizadoEm,
            'resultado' => 'COMPLETA',
        ]);
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Report Turno',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Report Turno',
            'email' => 'report.turno.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
