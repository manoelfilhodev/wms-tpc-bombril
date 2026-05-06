<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandasFiltroStatusMultiploTest extends TestCase
{
    public function test_filtro_de_demandas_aceita_multiplos_status_de_separacao(): void
    {
        $this->actingAs($this->createUser());

        Demanda::create($this->demandaData('DT-A-SEPARAR-001', 'A_SEPARAR'));
        Demanda::create($this->demandaData('DT-SEPARANDO-001', 'SEPARANDO', [
            'separacao_iniciada_em' => now(),
        ]));
        Demanda::create($this->demandaData('DT-SEPARADO-001', 'CONFERIDO', [
            'separacao_finalizada_em' => now(),
            'separacao_resultado' => 'COMPLETA',
        ]));

        $this->get(route('demandas.index', [
            'status' => ['A_SEPARAR', 'SEPARANDO'],
        ]))
            ->assertOk()
            ->assertSee('DT-A-SEPARAR-001')
            ->assertSee('DT-SEPARANDO-001')
            ->assertDontSee('DT-SEPARADO-001');
    }

    public function test_filtro_de_demandas_mantem_compatibilidade_com_status_unico(): void
    {
        $this->actingAs($this->createUser());

        Demanda::create($this->demandaData('DT-A-SEPARAR-002', 'A_SEPARAR'));
        Demanda::create($this->demandaData('DT-SEPARANDO-002', 'SEPARANDO', [
            'separacao_iniciada_em' => now(),
        ]));

        $this->get(route('demandas.index', ['status' => 'SEPARANDO']))
            ->assertOk()
            ->assertDontSee('DT-A-SEPARAR-002')
            ->assertSee('DT-SEPARANDO-002');
    }

    public function test_filtro_a_separar_nao_retorna_dt_ja_separada_mesmo_com_status_antigo(): void
    {
        $this->actingAs($this->createUser());

        $demanda = Demanda::create($this->demandaData('DT-JA-SEPARADA-001', 'A_SEPARAR', [
            'possui_sobra' => true,
            'separacao_finalizada_em' => '2026-04-29 09:00:00',
            'separacao_resultado' => 'COMPLETA',
        ]));
        $demanda->forceFill(['created_at' => '2026-04-29 08:00:00'])->save();

        $this->get(route('demandas.index', [
            'somente_sobra' => 1,
            'data_inicio' => '2026-04-29',
            'data_fim' => '2026-04-29',
            'status' => ['A_SEPARAR'],
        ]))
            ->assertOk()
            ->assertDontSee('DT-JA-SEPARADA-001')
            ->assertSee('Nenhuma DT encontrada com status A separar neste período.');
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Demanda',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Demanda',
            'email' => 'demanda.status.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }

    private function demandaData(string $fo, string $status, array $extra = []): array
    {
        return array_merge([
            'fo' => $fo,
            'cliente' => 'Cliente Teste',
            'transportadora' => 'Transportadora Teste',
            'tipo' => 'EXPEDICAO',
            'status' => $status,
            'quantidade' => 1,
            'possui_sobra' => false,
        ], $extra);
    }
}
