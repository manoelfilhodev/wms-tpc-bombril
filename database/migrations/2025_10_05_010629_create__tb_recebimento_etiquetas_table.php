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
        Schema::create('_tb_recebimento_etiquetas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_recebimento')->index();
            $table->string('sku', 100);
            $table->string('ean', 50);
            $table->string('descricao');
            $table->string('ua', 100);
            $table->integer('lastro');
            $table->integer('camada');
            $table->integer('paletizacao');
            $table->integer('numero_etiqueta');
            $table->integer('total_etiquetas');
            $table->timestamp('data_geracao')->useCurrent();
            $table->integer('quantidade')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_recebimento_etiquetas');
    }
};
