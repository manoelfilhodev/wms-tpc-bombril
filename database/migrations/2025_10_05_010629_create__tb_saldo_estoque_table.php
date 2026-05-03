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
        Schema::create('_tb_saldo_estoque', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sku_id');
            $table->integer('posicao_id')->index();
            $table->integer('quantidade')->default(0);
            $table->integer('unidade_id')->default(2);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['sku_id', 'posicao_id'], 'unq_sku_posicao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_saldo_estoque');
    }
};
