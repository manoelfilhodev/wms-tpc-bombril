<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->enum('tipo_demanda', ['PROGRAMADA', 'OPORTUNIDADE'])
                ->default('PROGRAMADA')
                ->after('possui_picking');
            $table->string('origem_demanda', 50)
                ->nullable()
                ->after('tipo_demanda');

            $table->index(['tipo_demanda', 'agenda_entrega_em'], 'idx_exp_prog_tipo_agenda');
        });
    }

    public function down(): void
    {
        Schema::table('_tb_expedicao_programacoes', function (Blueprint $table) {
            $table->dropIndex('idx_exp_prog_tipo_agenda');
            $table->dropColumn(['tipo_demanda', 'origem_demanda']);
        });
    }
};
