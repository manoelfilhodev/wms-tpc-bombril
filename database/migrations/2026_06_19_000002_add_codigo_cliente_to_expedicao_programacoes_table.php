<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('_tb_expedicao_programacoes', 'codigo_cliente')) {
            return;
        }

        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->string('codigo_cliente', 50)->nullable()->after('uf_destino');
            $table->index('codigo_cliente', 'idx_exp_prog_codigo_cliente');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('_tb_expedicao_programacoes', 'codigo_cliente')) {
            return;
        }

        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->dropIndex('idx_exp_prog_codigo_cliente');
            $table->dropColumn('codigo_cliente');
        });
    }
};
