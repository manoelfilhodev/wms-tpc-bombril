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
        Schema::create('_tb_relatorio_mb51', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sku', 100)->nullable();
            $table->string('descricao')->nullable();
            $table->string('tipo_movimento', 10)->nullable();
            $table->string('posicao', 50)->nullable();
            $table->date('data_importacao')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_relatorio_mb51');
    }
};
