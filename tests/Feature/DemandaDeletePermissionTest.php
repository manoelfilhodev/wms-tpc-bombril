<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaDeletePermissionTest extends TestCase
{
    public function test_admin_e_gestor_veem_lixeira_na_tela_operacional(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-ADMIN'));

        $admin = $this->createUser('admin', 'Admin');
        $this->actingAs($admin)
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin'])
            ->get(route('demandas.operacional'))
            ->assertOk()
            ->assertSee("form-delete-demanda-{$demanda->id}", false)
            ->assertSee('mdi-trash-can-outline', false);

        $gestor = $this->createUser('gestor', 'Gestor');
        $this->actingAs($gestor)
            ->withSession(['tipo' => 'gestor', 'nivel' => 'Gestor'])
            ->get(route('demandas.operacional'))
            ->assertOk()
            ->assertSee("form-delete-demanda-{$demanda->id}", false)
            ->assertSee('mdi-trash-can-outline', false);
    }

    public function test_operador_nao_ve_lixeira_e_nao_consegue_excluir_dt(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-OPERADOR'));
        $operador = $this->createUser('operador', 'Operador');

        $this->actingAs($operador)
            ->withSession(['tipo' => 'operador', 'nivel' => 'Operador'])
            ->get(route('demandas.operacional'))
            ->assertOk()
            ->assertDontSee("form-delete-demanda-{$demanda->id}", false)
            ->assertDontSee('mdi-trash-can-outline', false);

        $this->actingAs($operador)
            ->withSession(['tipo' => 'operador', 'nivel' => 'Operador'])
            ->delete(route('demandas.destroy', $demanda->id))
            ->assertForbidden();

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $demanda->id,
            'fo' => 'DT-DELETE-OPERADOR',
        ]);
    }

    public function test_gestor_consegue_excluir_dt(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-GESTOR'));
        $gestor = $this->createUser('gestor', 'Gestor');

        $this->actingAs($gestor)
            ->withSession(['tipo' => 'gestor', 'nivel' => 'Gestor'])
            ->delete(route('demandas.destroy', $demanda->id), [
                'password' => 'Secret123!',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('_tb_demanda', [
            'id' => $demanda->id,
        ]);
    }

    public function test_senha_incorreta_nao_exclui_dt(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-SENHA'));
        $admin = $this->createUser('admin', 'Admin');

        $this->actingAs($admin)
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin'])
            ->delete(route('demandas.destroy', $demanda->id), [
                'password' => 'SenhaErrada!',
            ])
            ->assertSessionHas('error', 'Senha inválida para o usuário logado. A DT não foi excluída.');

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $demanda->id,
            'fo' => 'DT-DELETE-SENHA',
        ]);
    }

    public function test_usuario_sem_senha_local_nao_exclui_dt(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-SEM-SENHA'));
        $gestor = $this->createUser('gestor', 'Gestor');
        $gestor->forceFill(['password' => null])->save();

        $this->actingAs($gestor)
            ->withSession(['tipo' => 'gestor', 'nivel' => 'Gestor'])
            ->delete(route('demandas.destroy', $demanda->id), [
                'password' => 'Secret123!',
            ])
            ->assertSessionHas('error', 'Este usuário não possui senha local cadastrada para confirmar a exclusão.');

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $demanda->id,
            'fo' => 'DT-DELETE-SEM-SENHA',
        ]);
    }

    private function demandaData(string $fo): array
    {
        return [
            'fo' => $fo,
            'cliente' => 'Cliente Delete',
            'transportadora' => 'Transportadora Delete',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
            'quantidade' => 1,
            'possui_sobra' => true,
        ];
    }

    private function createUser(string $tipo, string $nivel): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Delete ' . uniqid(),
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => "Usuario {$nivel} Delete",
            'email' => 'delete.' . $tipo . '.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'status' => 'ativo',
            'nivel' => $nivel,
        ]);
    }
}
