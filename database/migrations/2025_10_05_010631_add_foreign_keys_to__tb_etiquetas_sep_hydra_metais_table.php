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
        Schema::table('_tb_etiquetas_sep_hydra_metais', function (Blueprint $table) {
            $table->foreign(['pedido_id'], 'fk_pedido_etiqueta')->references(['id'])->on('_tb_pedidos')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['usuario_id'], 'fk_usuario_etiqueta')->references(['id_user'])->on('_tb_usuarios')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_tb_etiquetas_sep_hydra_metais', function (Blueprint $table) {
            $table->dropForeign('fk_pedido_etiqueta');
            $table->dropForeign('fk_usuario_etiqueta');
        });
    }
};
