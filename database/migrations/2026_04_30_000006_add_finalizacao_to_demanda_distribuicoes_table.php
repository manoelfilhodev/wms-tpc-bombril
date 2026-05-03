<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_demanda_distribuicoes', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_demanda_distribuicoes', 'finalizado_em')) {
                $table->timestamp('finalizado_em')->nullable()->after('quantidade_pecas');
            }
            if (!Schema::hasColumn('_tb_demanda_distribuicoes', 'resultado')) {
                $table->string('resultado', 20)->nullable()->after('finalizado_em');
            }
            $table->index(['demanda_id', 'separador_nome']);
        });
    }

    public function down(): void
    {
        Schema::table('_tb_demanda_distribuicoes', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_demanda_distribuicoes', 'resultado')) {
                $table->dropColumn('resultado');
            }
            if (Schema::hasColumn('_tb_demanda_distribuicoes', 'finalizado_em')) {
                $table->dropColumn('finalizado_em');
            }
        });
    }
};
