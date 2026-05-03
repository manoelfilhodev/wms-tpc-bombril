<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_posicoes', function (Blueprint $table) {
            // Se sua tabela não tiver essas colunas básicas, podemos criá-las.
            if (!Schema::hasColumn('_tb_posicoes', 'corredor')) {
                $table->string('corredor', 20)->nullable();
            }
            if (!Schema::hasColumn('_tb_posicoes', 'prateleira')) {
                $table->string('prateleira', 20)->nullable();
            }
            if (!Schema::hasColumn('_tb_posicoes', 'nivel')) {
                $table->string('nivel', 20)->nullable();
            }

            // Agora as colunas novas, sem usar "after" para evitar erro quando a coluna de referência não existe.
            if (!Schema::hasColumn('_tb_posicoes', 'capacidade')) {
                $table->integer('capacidade')->nullable();
            }
            if (!Schema::hasColumn('_tb_posicoes', 'tipo_armazenamento')) {
                $table->string('tipo_armazenamento', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_posicoes', function (Blueprint $table) {
            // Remova só o que adicionamos aqui (capacidade e tipo_armazenamento).
            if (Schema::hasColumn('_tb_posicoes', 'tipo_armazenamento')) {
                $table->dropColumn('tipo_armazenamento');
            }
            if (Schema::hasColumn('_tb_posicoes', 'capacidade')) {
                $table->dropColumn('capacidade');
            }

            // Se você criou corredor/prateleira/nivel aqui e quiser remover no rollback, descomente:
            // foreach (['nivel','prateleira','corredor'] as $col) {
            //     if (Schema::hasColumn('_tb_posicoes', $col)) {
            //         $table->dropColumn($col);
            //     }
            // }
        });
    }
};
