<?php

namespace Tests\Feature;

use App\Models\Demanda;
use App\Models\DemandaItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemandaImportacaoDuplicadaTest extends TestCase
{
    public function test_importacao_nao_sobrescreve_dt_ja_existente(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        $demandaExistente = Demanda::create([
            'fo' => '251399001',
            'cliente' => 'Cliente Original',
            'transportadora' => 'Transportadora Original',
            'tipo' => 'EXPEDICAO',
            'status' => 'SEPARANDO',
            'possui_sobra' => true,
            'total_itens' => 1,
            'total_itens_com_sobra' => 1,
        ]);

        DemandaItem::create([
            'demanda_id' => $demandaExistente->id,
            'sku' => 'SKU-ORIGINAL',
            'sku_normalizado' => '999',
            'descricao' => 'Item original',
            'unidade_medida' => 'CX',
            'sobra' => 10,
            'bloqueado' => false,
        ]);

        $this->post(route('demandas.import'), [
            'planilha' => implode("\n", [
                "Transporte\tMaterial\tSobra\tNome\tTransportadora\tUnid.medida básica\tTexto breve material",
                "251399001\t000123\t50\tCliente Novo\tTransportadora Nova\tCX\tItem duplicado",
                "251399002\t000456\t25\tCliente Novo\tTransportadora Nova\tCX\tItem novo",
            ]),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('_tb_demanda', [
            'id' => $demandaExistente->id,
            'fo' => '251399001',
            'cliente' => 'Cliente Original',
            'transportadora' => 'Transportadora Original',
            'status' => 'SEPARANDO',
        ]);
        $this->assertDatabaseHas('_tb_demanda_itens', [
            'demanda_id' => $demandaExistente->id,
            'sku' => 'SKU-ORIGINAL',
            'sobra' => 10,
        ]);
        $this->assertDatabaseMissing('_tb_demanda_itens', [
            'demanda_id' => $demandaExistente->id,
            'sku' => '000123',
        ]);
        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => '251399002',
            'cliente' => 'Cliente Novo',
            'transportadora' => 'Transportadora Nova',
        ]);
        $this->assertDatabaseCount('_tb_demanda', 2);
    }

    public function test_importacao_com_apenas_dts_existentes_e_bloqueada(): void
    {
        $this->actingAs($this->createUser())
            ->withSession(['tipo' => 'admin', 'nivel' => 'Admin']);

        Demanda::create([
            'fo' => '251399003',
            'cliente' => 'Cliente Original',
            'transportadora' => 'Transportadora Original',
            'tipo' => 'EXPEDICAO',
            'status' => 'A_SEPARAR',
        ]);

        $this->post(route('demandas.import'), [
            'planilha' => implode("\n", [
                "Transporte\tMaterial\tSobra\tNome\tTransportadora\tUnid.medida básica\tTexto breve material",
                "251399003\t000123\t50\tCliente Novo\tTransportadora Nova\tCX\tItem duplicado",
            ]),
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('_tb_demanda', [
            'fo' => '251399003',
            'cliente' => 'Cliente Original',
            'transportadora' => 'Transportadora Original',
        ]);
        $this->assertDatabaseMissing('_tb_demanda_itens', [
            'sku' => '000123',
        ]);
    }

    private function createUser(): User
    {
        $unidadeId = DB::table('_tb_unidades')->insertGetId([
            'nome' => 'Unidade Teste Importacao',
            'status' => 'ativo',
            'created_at' => now(),
        ]);

        return User::create([
            'nome' => 'Usuario Teste Importacao',
            'email' => 'importacao.' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'unidade_id' => $unidadeId,
            'tipo' => 'admin',
            'status' => 'ativo',
            'nivel' => 'Admin',
        ]);
    }
}
