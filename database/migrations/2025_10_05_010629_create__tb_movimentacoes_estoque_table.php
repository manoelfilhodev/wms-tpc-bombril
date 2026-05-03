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
        Schema::create('_tb_movimentacoes_estoque', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sku_id');
            $table->integer('posicao_id');
            $table->integer('unidade_id');
            $table->enum('tipo', ['ENTRADA', 'SAIDA']);
            $table->integer('quantidade');
            $table->string('origem', 50)->nullable();
            $table->integer('referencia_id')->nullable();
            $table->integer('usuario_id');
            $table->text('observacoes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_movimentacoes_estoque');
    }
};
