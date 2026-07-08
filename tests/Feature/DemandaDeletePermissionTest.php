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
    public function test_usuario_operacional_ve_lixeira_na_tela_operacional(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-ADMIN'));

        $operador = $this->createUser('operador', 'Operador');
        $this->actingAs($operador)
            ->withSession(['tipo' => 'operador', 'nivel' => 'Operador'])
            ->get(route('demandas.operacional'))
            ->assertOk()
            ->assertSee("form-delete-demanda-{$demanda->id}", false)
            ->assertSee('mdi-trash-can-outline', false)
            ->assertSee('Esta ação não pode ser desfeita.')
            ->assertSee('forma irreversível')
            ->assertDontSee('Digite a senha de um administrador');
    }

    public function test_operador_consegue_excluir_dt_sem_senha(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-OPERADOR'));
        $operador = $this->createUser('operador', 'Operador');

        $this->actingAs($operador)
            ->withSession(['tipo' => 'operador', 'nivel' => 'Operador'])
            ->delete(route('demandas.destroy', $demanda->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('_tb_demanda', [
            'id' => $demanda->id,
        ]);
    }

    public function test_gestor_consegue_excluir_dt_sem_senha(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-GESTOR'));
        $gestor = $this->createUser('gestor', 'Gestor');

        $this->actingAs($gestor)
            ->withSession(['tipo' => 'gestor', 'nivel' => 'Gestor'])
            ->delete(route('demandas.destroy', $demanda->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('_tb_demanda', [
            'id' => $demanda->id,
        ]);
    }

    public function test_admin_consegue_excluir_dt_sem_senha(): void
    {
        $this->seed(RbacSeeder::class);
        $demanda = Demanda::create($this->demandaData('DT-DELETE-SEM-SENHA'));
        $admin = $this->createUser('admin', 'Admin');

        $this->actingAs($admin)
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin'])
            ->delete(route('demandas.destroy', $demanda->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('_tb_demanda', [
            'id' => $demanda->id,
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
