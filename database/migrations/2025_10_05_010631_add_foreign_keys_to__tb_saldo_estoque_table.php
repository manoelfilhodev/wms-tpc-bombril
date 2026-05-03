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
        Schema::table('_tb_saldo_estoque', function (Blueprint $table) {
            $table->foreign(['sku_id'], '_tb_saldo_estoque_ibfk_1')->references(['id'])->on('_tb_materiais')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['posicao_id'], '_tb_saldo_estoque_ibfk_2')->references(['id'])->on('_tb_posicoes')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_saldo_estoque', function (Blueprint $table) {
            $table->dropForeign('_tb_saldo_estoque_ibfk_1');
            $table->dropForeign('_tb_saldo_estoque_ibfk_2');
        });
    }
};
