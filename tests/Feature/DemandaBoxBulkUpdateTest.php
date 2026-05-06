<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaBoxBulkUpdateTest extends TestCase
{
    public function test_boxes_podem_ser_salvos_em_lote(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $primeira = Demanda::create($this->demandaData('DT-BOX-001'));
        $segunda = Demanda::create($this->demandaData('DT-BOX-002', ['stage' => 'BOX ANTIGO']));
        $terceira = Demanda::create($this->demandaData('DT-BOX-003', ['stage' => 'BOX MANTIDO']));

        $this->patch(route('demandas.updateStagesMultiple'), [
            'stages' => [
                $primeira->id => 'BOX 01',
                $segunda->id => 'BOX 02',
                $terceira->id => 'BOX MANTIDO',
            ],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $primeira->id,
            'stage' => 'BOX 01',
        ]);
        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $segunda->id,
            'stage' => 'BOX 02',
        ]);
        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $terceira->id,
            'stage' => 'BOX MANTIDO',
        ]);
    }

    public function test_box_vazio_em_lote_limpa_o_campo(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = Demanda::create($this->demandaData('DT-BOX-004', ['stage' => 'BOX 04']));

        $this->patch(route('demandas.updateStagesMultiple'), [
            'stages' => [
                $demanda->id => '   ',
            ],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $demanda->id,
            'stage' => null,
        ]);
    }

    private function demandaData(string $fo, array $extra = []): array
    {
        return array_merge([
            'fo' => $fo,
            'cliente' => 'Cliente Box',
            'transportadora' => 'Transportadora Box',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
            'quantidade' => 1,
            'possui_sobra' => true,
        ], $extra);
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Box',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Box',
            'email' => 'box.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
