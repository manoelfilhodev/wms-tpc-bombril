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
        Schema::table('_tb_notificacoes', function (Blueprint $table) {
            $table->foreign(['destino_unidade_id'], '_tb_notificacoes_ibfk_1')->references(['id'])->on('_tb_unidades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['destino_usuario_id'], '_tb_notificacoes_ibfk_2')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_notificacoes', function (Blueprint $table) {
            $table->dropForeign('_tb_notificacoes_ibfk_1');
            $table->dropForeign('_tb_notificacoes_ibfk_2');
        });
    }
};
