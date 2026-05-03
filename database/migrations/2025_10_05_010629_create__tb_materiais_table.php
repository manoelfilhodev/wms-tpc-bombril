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
        Schema::create('_tb_materiais', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('unidade_id')->index();
            $table->string('sku', 50)->index();
            $table->string('descricao')->nullable();
            $table->integer('categoria_id')->nullable()->index();
            $table->integer('quantidade_estoque')->nullable()->default(0);
            $table->integer('lastro')->default(0);
            $table->integer('camada')->default(0);
            $table->integer('paletizacao')->default(0);
            $table->string('ean')->default('0');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_materiais');
    }
};
