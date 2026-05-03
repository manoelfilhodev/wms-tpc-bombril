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
        Schema::create('_tb_contagem_livre', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('ficha', 50)->nullable();
            $table->string('sku', 50)->nullable();
            $table->integer('quantidade')->nullable();
            $table->integer('contado_por')->nullable()->index();
            $table->timestamp('data_hora')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_contagem_livre');
    }
};
