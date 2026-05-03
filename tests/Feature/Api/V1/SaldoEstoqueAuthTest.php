<?php

namespace Tests\Feature\Api\V1;

use Tests\Feature\Api\V1\Concerns\CreatesSaldoEstoqueData;
use Tests\TestCase;

class SaldoEstoqueAuthTest extends TestCase
{
    use CreatesSaldoEstoqueData;

    public function test_index_requires_token(): void
    {
        $this->getJson('/api/v1/saldo-estoque')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
