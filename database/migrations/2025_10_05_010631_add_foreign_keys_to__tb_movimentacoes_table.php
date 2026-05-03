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
        Schema::table('_tb_movimentacoes', function (Blueprint $table) {
            $table->foreign(['unidade_id'], '_tb_movimentacoes_ibfk_1')->references(['id'])->on('_tb_unidades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['material_id'], '_tb_movimentacoes_ibfk_2')->references(['id'])->on('_tb_materiais')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['usuario_id'], '_tb_movimentacoes_ibfk_3')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_movimentacoes', function (Blueprint $table) {
            $table->dropForeign('_tb_movimentacoes_ibfk_1');
            $table->dropForeign('_tb_movimentacoes_ibfk_2');
            $table->dropForeign('_tb_movimentacoes_ibfk_3');
        });
    }
};
