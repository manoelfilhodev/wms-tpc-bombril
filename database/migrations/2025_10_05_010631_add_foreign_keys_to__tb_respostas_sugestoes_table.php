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
        Schema::table('_tb_respostas_sugestoes', function (Blueprint $table) {
            $table->foreign(['sugestao_id'], '_tb_respostas_sugestoes_ibfk_1')->references(['id'])->on('_tb_sugestoes_atualizacoes')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['respondido_por'], '_tb_respostas_sugestoes_ibfk_2')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_respostas_sugestoes', function (Blueprint $table) {
            $table->dropForeign('_tb_respostas_sugestoes_ibfk_1');
            $table->dropForeign('_tb_respostas_sugestoes_ibfk_2');
        });
    }
};
