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
        Schema::create('_tb_kit_etiquetas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_kit')->index();
            $table->string('sku', 100);
            $table->string('ean', 50)->nullable();
            $table->string('descricao');
            $table->string('ua', 100)->nullable();
            $table->integer('lastro')->nullable();
            $table->integer('camada')->nullable();
            $table->integer('paletizacao')->nullable();
            $table->integer('numero_etiqueta');
            $table->integer('total_etiquetas');
            $table->timestamp('data_geracao')->useCurrent();
            $table->integer('quantidade');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_kit_etiquetas');
    }
};
