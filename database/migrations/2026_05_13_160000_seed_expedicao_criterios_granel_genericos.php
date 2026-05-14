<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $criterios = [
        [
            'categoria' => 'SEPARACAO',
            'nome' => 'Separação granel genérica',
            'sku_min' => null,
            'sku_max' => null,
            'tempo_previsto_minutos' => 90,
        ],
        [
            'categoria' => 'CONFERENCIA',
            'nome' => 'Conferência granel genérica',
            'sku_min' => null,
            'sku_max' => null,
            'tempo_previsto_minutos' => 60,
        ],
        [
            'categoria' => 'CARREGAMENTO',
            'nome' => 'Carregamento granel genérico',
            'sku_min' => null,
            'sku_max' => null,
            'tempo_previsto_minutos' => 120,
        ],
    ];

    public function up(): void
    {
        foreach ($this->criterios as $criterio) {
            $existe = DB::table('_tb_expedicao_criterios')
                ->where('categoria', $criterio['categoria'])
                ->where('tipo_carga', 'GRANEL')
                ->whereNull('sku_min')
                ->whereNull('sku_max')
                ->exists();

            if ($existe) {
                continue;
            }

            DB::table('_tb_expedicao_criterios')->insert(array_merge($criterio, [
                'tipo_carga' => 'GRANEL',
                'possui_picking' => null,
                'volume_min' => null,
                'volume_max' => null,
                'peso_min' => null,
                'peso_max' => null,
                'multiplicador' => 1,
                'ativo' => true,
                'observacoes' => 'Critério genérico criado para cargas granel importadas pela base PROG.',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        foreach ($this->criterios as $criterio) {
            DB::table('_tb_expedicao_criterios')
                ->where('categoria', $criterio['categoria'])
                ->where('tipo_carga', 'GRANEL')
                ->where('nome', $criterio['nome'])
                ->where('observacoes', 'Critério genérico criado para cargas granel importadas pela base PROG.')
                ->delete();
        }
    }
};
