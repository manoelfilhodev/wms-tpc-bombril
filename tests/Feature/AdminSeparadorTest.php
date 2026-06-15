<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeparadorTest extends TestCase
{
    public function test_crud_de_separadores_gera_logs_de_auditoria(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->withSession([
            'tipo' => 'admin',
            'nivel' => 'Admin',
            'user_id' => $admin->id_user,
            'nome' => $admin->nome,
            'unidade' => $admin->unidade_id,
        ]);

        $this->get(route('separadores.index'))
            ->assertOk()
            ->assertSee('Lista de Separadores');

        $this->post(route('separadores.store'), [
            'chapa' => 'SEP-001',
            'nome' => 'Separador Auditavel',
            'cargo' => 'Separador',
            'turno' => '1 turno',
        ])->assertRedirect(route('separadores.index'));

        $separadorId = DB::table('_tb_separadores')->where('chapa', 'SEP-001')->value('id');

        $this->put(route('separadores.update', $separadorId), [
            'chapa' => 'SEP-001',
            'nome' => 'Separador Auditavel Editado',
            'cargo' => 'Conferente',
            'turno' => '2 turno',
        ])->assertRedirect(route('separadores.index'));

        $this->delete(route('separadores.destroy', $separadorId))
            ->assertRedirect(route('separadores.index'));

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'separador_criado',
            'entity_type' => 'separador',
            'entity_id' => (string) $separadorId,
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'separador_atualizado',
            'entity_type' => 'separador',
            'entity_id' => (string) $separadorId,
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'separador_excluido',
            'entity_type' => 'separador',
            'entity_id' => (string) $separadorId,
        ]);
    }

    private function createAdmin(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Separadores Admin',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Admin Separadores',
            'email' => 'admin.separadores.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
