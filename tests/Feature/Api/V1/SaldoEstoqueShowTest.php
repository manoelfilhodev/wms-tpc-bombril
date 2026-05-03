<?php

namespace Tests\Feature\Api\V1;

use Tests\Feature\Api\V1\Concerns\CreatesSaldoEstoqueData;
use Tests\TestCase;

class SaldoEstoqueShowTest extends TestCase
{
    use CreatesSaldoEstoqueData;

    public function test_show_returns_404_when_not_found(): void
    {
        $token = $this->createAuthToken();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque/999999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_show_returns_200_when_found(): void
    {
        $token = $this->createAuthToken();
        $id = $this->createSaldo(['quantidade' => 120]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/saldo-estoque/' . $id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.quantidade', 120);
    }
}
