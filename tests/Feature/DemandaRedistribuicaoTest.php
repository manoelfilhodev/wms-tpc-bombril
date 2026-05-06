<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\DemandaDistribuicao;
use App\Models\DemandaItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaRedistribuicaoTest extends TestCase
{
    public function test_distribuicao_aberta_pode_ser_reduzida_e_saldo_redistribuido(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = $this->createDemandaComPicking(500, 5);
        $joao = DemandaDistribuicao::create([
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Joao',
            'quantidade_pecas' => 500,
            'quantidade_skus' => 5,
        ]);

        $this->patch(route('demandas.redistribuirDistribuicao', [$demanda->id, $joao->id]), [
            'quantidade_pecas' => 300,
            'quantidade_skus' => 3,
        ])->assertSessionHas('success');

        $this->post(route('demandas.distribuir', $demanda->id), [
            'separador_nome' => 'Maria',
            'quantidade_pecas' => 200,
            'quantidade_skus' => 2,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda_distribuicoes', [
            'id' => $joao->id,
            'separador_nome' => 'Joao',
            'quantidade_pecas' => 300,
            'quantidade_skus' => 3,
            'finalizado_em' => null,
        ]);
        $this->assertDatabaseHas('_tb_demanda_distribuicoes', [
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Maria',
            'quantidade_pecas' => 200,
            'quantidade_skus' => 2,
            'finalizado_em' => null,
        ]);
    }

    public function test_distribuicao_aceita_multiplas_linhas_no_mesmo_envio(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = $this->createDemandaComPicking(500, 5);

        $this->post(route('demandas.distribuir', $demanda->id), [
            'distribuicoes' => [
                [
                    'separador_nome' => 'Joao',
                    'quantidade_pecas' => 300,
                    'quantidade_skus' => 3,
                ],
                [
                    'separador_nome' => 'Maria',
                    'quantidade_pecas' => 200,
                    'quantidade_skus' => 2,
                ],
            ],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda_distribuicoes', [
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Joao',
            'quantidade_pecas' => 300,
            'quantidade_skus' => 3,
            'finalizado_em' => null,
        ]);
        $this->assertDatabaseHas('_tb_demanda_distribuicoes', [
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Maria',
            'quantidade_pecas' => 200,
            'quantidade_skus' => 2,
            'finalizado_em' => null,
        ]);
    }

    public function test_distribuicao_finalizada_nao_pode_ser_redistribuida(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = $this->createDemandaComPicking(500, 5);
        $joao = DemandaDistribuicao::create([
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Joao',
            'quantidade_pecas' => 500,
            'quantidade_skus' => 5,
            'finalizado_em' => now(),
            'resultado' => 'COMPLETA',
        ]);

        $this->patch(route('demandas.redistribuirDistribuicao', [$demanda->id, $joao->id]), [
            'quantidade_pecas' => 300,
            'quantidade_skus' => 3,
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('_tb_demanda_distribuicoes', [
            'id' => $joao->id,
            'quantidade_pecas' => 500,
            'quantidade_skus' => 5,
        ]);
    }

    public function test_redistribuicao_aberta_pode_ser_removida_para_liberar_saldo(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demanda = $this->createDemandaComPicking(500, 5);
        $joao = DemandaDistribuicao::create([
            'demanda_id' => $demanda->id,
            'separador_nome' => 'Joao',
            'quantidade_pecas' => 500,
            'quantidade_skus' => 5,
        ]);

        $this->patch(route('demandas.redistribuirDistribuicao', [$demanda->id, $joao->id]), [
            'quantidade_pecas' => 0,
            'quantidade_skus' => 0,
        ])->assertSessionHas('success');

        $this->assertDatabaseMissing('_tb_demanda_distribuicoes', [
            'id' => $joao->id,
        ]);
    }

    private function createDemandaComPicking(int $pecas, int $skus): Demanda
    {
        $demanda = Demanda::create([
            'fo' => 'DT-REDIST-' . uniqid(),
            'cliente' => 'Cliente Redistribuicao',
            'transportadora' => 'Transportadora Redistribuicao',
            'tipo' => 'EXPEDICAO',
            'status' => 'SEPARANDO',
            'quantidade' => $pecas,
            'possui_sobra' => true,
            'separacao_iniciada_em' => now(),
        ]);

        $pecasPorSku = intdiv($pecas, $skus);
        for ($i = 1; $i <= $skus; $i++) {
            DemandaItem::create([
                'demanda_id' => $demanda->id,
                'sku' => 'SKU-' . $i,
                'sku_normalizado' => 'SKU-' . $i,
                'descricao' => 'Item ' . $i,
                'unidade_medida' => 'CX',
                'sobra' => $pecasPorSku,
                'bloqueado' => false,
            ]);
        }

        return $demanda;
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Redistribuicao',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Redistribuicao',
            'email' => 'redistribuicao.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
