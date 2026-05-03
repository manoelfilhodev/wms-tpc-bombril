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
        Schema::create('_tb_doca_saida', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sku_id')->index();
            $table->integer('quantidade');
            $table->string('posicao', 50)->nullable()->index();
            $table->integer('unidade_id');
            $table->integer('usuario_id');
            $table->integer('pedido_id')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_doca_saida');
    }
};
