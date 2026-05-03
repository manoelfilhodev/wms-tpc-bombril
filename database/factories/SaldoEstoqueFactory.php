<?php

namespace Database\Factories;

use App\Models\SaldoEstoque;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<SaldoEstoque>
 */
class SaldoEstoqueFactory extends Factory
{
    protected $model = SaldoEstoque::class;

    public function definition(): array
    {
        $unidadeId = DB::table('_tb_unidades')->value('id');

        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->insertGetId([
                'nome' => 'Unidade Factory',
                'status' => 'ativo',
                'created_at' => now(),
            ]);
        }

        $materialId = DB::table('_tb_materiais')->insertGetId([
            'nome' => 'Material ' . $this->faker->unique()->numerify('###'),
            'unidade_id' => $unidadeId,
            'sku' => 'SKU' . $this->faker->unique()->numerify('######'),
            'descricao' => $this->faker->sentence(4),
            'ean' => (string) $this->faker->unique()->numerify('###########'),
            'created_at' => now(),
            'status' => 'ativo',
        ]);

        $posicaoId = DB::table('_tb_posicoes')->insertGetId([
            'codigo_posicao' => 'POS-' . $this->faker->unique()->numerify('######'),
            'setor' => 'A',
            'unidade_id' => $unidadeId,
            'status' => 'ativa',
            'created_at' => now(),
        ]);

        return [
            'sku_id' => $materialId,
            'material_id' => $materialId,
            'posicao_id' => $posicaoId,
            'unidade_id' => $unidadeId,
            'quantidade' => $this->faker->numberBetween(1, 500),
            'data_entrada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
