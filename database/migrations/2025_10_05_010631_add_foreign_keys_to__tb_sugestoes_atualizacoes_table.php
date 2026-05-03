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
        Schema::table('_tb_sugestoes_atualizacoes', function (Blueprint $table) {
            $table->foreign(['criado_por'], '_tb_sugestoes_atualizacoes_ibfk_1')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_sugestoes_atualizacoes', function (Blueprint $table) {
            $table->dropForeign('_tb_sugestoes_atualizacoes_ibfk_1');
        });
    }
};
