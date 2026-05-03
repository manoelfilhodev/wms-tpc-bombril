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
        Schema::table('_tb_contagem_itens', function (Blueprint $table) {
            $table->foreign(['codigo_material'], 'fk_contagem_itens')->references(['codigo_material'])->on('_tb_itens_contagem')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['usuario_id'], '_tb_contagem_itens_ibfk_1')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_contagem_itens', function (Blueprint $table) {
            $table->dropForeign('fk_contagem_itens');
            $table->dropForeign('_tb_contagem_itens_ibfk_1');
        });
    }
};
