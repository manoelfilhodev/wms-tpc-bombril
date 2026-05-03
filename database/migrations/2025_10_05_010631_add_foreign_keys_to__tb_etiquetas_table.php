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
        Schema::table('_tb_etiquetas', function (Blueprint $table) {
            $table->foreign(['unidade_id'], '_tb_etiquetas_ibfk_1')->references(['id'])->on('_tb_unidades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['material_id'], '_tb_etiquetas_ibfk_2')->references(['id'])->on('_tb_materiais')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_etiquetas', function (Blueprint $table) {
            $table->dropForeign('_tb_etiquetas_ibfk_1');
            $table->dropForeign('_tb_etiquetas_ibfk_2');
        });
    }
};
