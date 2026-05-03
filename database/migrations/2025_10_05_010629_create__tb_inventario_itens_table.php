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
        Schema::create('_tb_inventario_itens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_inventario')->nullable()->index();
            $table->string('sku', 100)->nullable();
            $table->string('descricao')->nullable();
            $table->string('posicao', 50)->nullable();
            $table->integer('quantidade_sistema')->nullable();
            $table->integer('quantidade_fisica')->nullable();
            $table->integer('diferenca')->nullable()->storedAs('(`quantidade_fisica` - `quantidade_sistema`)');
            $table->enum('tipo_ajuste', ['nenhum', 'falta', 'sobra', 'ajuste_endereco'])->nullable();
            $table->boolean('necessita_ajuste')->nullable()->default(false);
            $table->boolean('ajustado')->nullable()->default(false);
            $table->timestamps();
            $table->integer('contado_por')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_inventario_itens');
    }
};
