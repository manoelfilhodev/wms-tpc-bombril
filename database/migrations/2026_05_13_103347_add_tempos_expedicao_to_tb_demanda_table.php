<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {

            $table->timestamp('conferencia_iniciada_em')->nullable()
                ->after('separacao_finalizada_em');

            $table->timestamp('conferencia_finalizada_em')->nullable()
                ->after('conferencia_iniciada_em');

            $table->timestamp('carregamento_iniciado_em')->nullable()
                ->after('conferencia_finalizada_em');

            $table->timestamp('carregamento_finalizado_em')->nullable()
                ->after('carregamento_iniciado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_demanda', function (Blueprint $table) {

            $table->dropColumn([
                'conferencia_iniciada_em',
                'conferencia_finalizada_em',
                'carregamento_iniciado_em',
                'carregamento_finalizado_em'
            ]);
        });
    }
};
