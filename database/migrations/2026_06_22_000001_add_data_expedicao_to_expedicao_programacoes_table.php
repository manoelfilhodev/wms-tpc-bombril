<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('_tb_expedicao_programacoes', 'data_expedicao_em')) {
            return;
        }

        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->dateTime('data_expedicao_em')->nullable()->after('agenda_entrega_em');
            $table->index('data_expedicao_em', 'idx_exp_prog_data_expedicao');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('_tb_expedicao_programacoes', 'data_expedicao_em')) {
            return;
        }

        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->dropIndex('idx_exp_prog_data_expedicao');
            $table->dropColumn('data_expedicao_em');
        });
    }
};
