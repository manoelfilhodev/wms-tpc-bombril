<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserAuditTest extends TestCase
{
    public function test_crud_de_usuarios_gera_logs_de_auditoria(): void
    {
        $admin = $this->createUser('admin');
        $unidadeNome = DB::table('_tb_unidades')->where('id', $admin->unidade_id)->value('nome');

        $this->actingAs($admin)->withSession([
            'tipo' => 'admin',
            'nivel' => 'Admin',
            'user_id' => $admin->id_user,
            'nome' => $admin->nome,
            'unidade' => $admin->unidade_id,
        ]);

        $this->post(route('usuarios.store'), [
            'nome' => 'Usuario Auditavel',
            'login' => 'usuario.auditavel@example.com',
            'senha' => 'Secret123!',
            'unidade' => $unidadeNome,
            'status' => '1',
            'cod_nivel' => 5,
            'desc_nivel' => 'Operador',
        ])->assertRedirect(route('usuarios.index'));

        $usuarioId = DB::table('_tb_usuarios')->where('email', 'usuario.auditavel@example.com')->value('id_user');

        $this->put(route('usuarios.update', $usuarioId), [
            'nome' => 'Usuario Auditavel Editado',
            'login' => 'usuario.auditavel.editado@example.com',
            'unidade' => $unidadeNome,
            'status' => '1',
            'tipo' => 'operador',
            'password' => 'NewSecret123!',
        ])->assertRedirect(route('usuarios.index'));

        $this->delete(route('usuarios.destroy', $usuarioId))
            ->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'usuario_criado',
            'entity_type' => 'usuario',
            'entity_id' => (string) $usuarioId,
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'usuario_atualizado',
            'entity_type' => 'usuario',
            'entity_id' => (string) $usuarioId,
        ]);

        $this->assertDatabaseHas('_tb_system_logs', [
            'module' => 'administracao',
            'action' => 'usuario_excluido',
            'entity_type' => 'usuario',
            'entity_id' => (string) $usuarioId,
        ]);
    }

    private function createUser(string $tipo): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Usuario Auditoria',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Admin Auditoria Usuarios',
            'email' => 'admin.usuarios.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => ucfirst($tipo),
        ]);
    }
}
