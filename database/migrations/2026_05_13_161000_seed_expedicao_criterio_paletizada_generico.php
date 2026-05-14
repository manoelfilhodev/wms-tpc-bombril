<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existe = DB::table('_tb_expedicao_criterios')
            ->where('categoria', 'SEPARACAO')
            ->where('tipo_carga', 'PALETIZADA')
            ->where('sku_min', 21)
            ->whereNull('sku_max')
            ->exists();

        if ($existe) {
            return;
        }

        DB::table('_tb_expedicao_criterios')->insert([
            'categoria' => 'SEPARACAO',
            'nome' => 'Separação paletizada genérica acima de 20 SKUs',
            'sku_min' => 21,
            'sku_max' => null,
            'volume_min' => null,
            'volume_max' => null,
            'peso_min' => null,
            'peso_max' => null,
            'tipo_carga' => 'PALETIZADA',
            'possui_picking' => null,
            'tempo_previsto_minutos' => 90,
            'multiplicador' => 1,
            'ativo' => true,
            'observacoes' => 'Fallback genérico para cargas paletizadas sem picking acima de 20 SKUs.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('_tb_expedicao_criterios')
            ->where('categoria', 'SEPARACAO')
            ->where('tipo_carga', 'PALETIZADA')
            ->where('nome', 'Separação paletizada genérica acima de 20 SKUs')
            ->where('observacoes', 'Fallback genérico para cargas paletizadas sem picking acima de 20 SKUs.')
            ->delete();
    }
};
