<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\V1\Concerns\CreatesSaldoEstoqueData;
use Tests\TestCase;

class SaldoEstoqueIndexTest extends TestCase
{
    use CreatesSaldoEstoqueData;

    public function test_index_returns_paginated_data_with_token(): void
    {
        $token = $this->createAuthToken();

        $this->createSaldo(['quantidade' => 10]);
        $this->createSaldo(['quantidade' => 20]);
        $this->createSaldo(['quantidade' => 30]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque?per_page=2&page=1')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_index_filters_work_for_sku_and_quantity_range(): void
    {
        $token = $this->createAuthToken();
        $unidade = $this->ensureUnidade();

        $materialA = $this->createMaterial($unidade, 'SKU-FILTRO-A', 'Parafuso A');
        $posicaoA = $this->createPosicao($unidade, 'A-01-01-' . uniqid());
        DB::table('_tb_saldo_estoque')->insert([
            'sku_id' => $materialA,
            'material_id' => $materialA,
            'posicao_id' => $posicaoA,
            'unidade_id' => $unidade,
            'quantidade' => 80,
            'data_entrada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialB = $this->createMaterial($unidade, 'SKU-FILTRO-B', 'Parafuso B');
        $posicaoB = $this->createPosicao($unidade, 'A-01-02-' . uniqid());
        DB::table('_tb_saldo_estoque')->insert([
            'sku_id' => $materialB,
            'material_id' => $materialB,
            'posicao_id' => $posicaoB,
            'unidade_id' => $unidade,
            'quantidade' => 5,
            'data_entrada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque?sku=SKU-FILTRO-A&min_qtd=50');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data');
        $this->assertNotEmpty($items);
        $this->assertSame('SKU-FILTRO-A', $items[0]['sku']);
        $this->assertGreaterThanOrEqual(50, $items[0]['quantidade']);
    }

    public function test_index_sorting_respects_allowlist(): void
    {
        $token = $this->createAuthToken();

        $this->createSaldo(['quantidade' => 15]);
        $this->createSaldo(['quantidade' => 55]);
        $this->createSaldo(['quantidade' => 35]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque?sort=quantidade&direction=desc&per_page=3');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual($data[1]['quantidade'], $data[0]['quantidade']);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque?sort=nao_permitido')
            ->assertStatus(422);
    }
}
