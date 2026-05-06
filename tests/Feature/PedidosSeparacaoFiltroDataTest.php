<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PedidosSeparacaoFiltroDataTest extends TestCase
{
    public function test_pedidos_pendentes_mantem_data_filtrada_na_sessao(): void
    {
        $this->actingAs($this->createUser());
        $this->createPedido('1001', '2026-05-01 08:00:00');
        $this->createPedido('2002', '2026-05-02 08:00:00');

        $this->get(route('pedidos.index', ['data' => '2026-05-01']))
            ->assertOk()
            ->assertSee('value="2026-05-01"', false)
            ->assertSee('#1001')
            ->assertDontSee('#2002');

        $this->get(route('pedidos.index'))
            ->assertOk()
            ->assertSee('value="2026-05-01"', false)
            ->assertSee('#1001')
            ->assertDontSee('#2002');
    }

    public function test_pedidos_pendentes_limpa_data_filtrada_quando_usuario_limpa_o_campo(): void
    {
        $this->actingAs($this->createUser());
        $this->withSession(['separacao_pedidos_filtro_data' => '2026-05-01']);
        $this->createPedido('1001', '2026-05-01 08:00:00');
        $this->createPedido('2002', '2026-05-02 08:00:00');

        $this->get(route('pedidos.index', ['data' => '']))
            ->assertOk()
            ->assertDontSee('value="2026-05-01"', false)
            ->assertSee('#1001')
            ->assertSee('#2002');
    }

    public function test_pedidos_pendentes_limpa_data_filtrada_pelo_botao_limpar(): void
    {
        $this->actingAs($this->createUser());
        $this->withSession(['separacao_pedidos_filtro_data' => '2026-05-01']);

        $this->get(route('pedidos.index', ['limpar_filtros' => 1]))
            ->assertRedirect(route('pedidos.index'));

        $this->assertFalse(session()->has('separacao_pedidos_filtro_data'));
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Separacao',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Separacao',
            'email' => 'separacao.filtro.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }

    private function createPedido(string $numero, string $dataCriacao): void
    {
        DB::table('_tb_pedidos')->insert([
            'numero_pedido' => $numero,
            'unidade_id' => 1,
            'status' => 'pendente',
            'criado_por' => 1,
            'data_criacao' => $dataCriacao,
        ]);
    }
}
