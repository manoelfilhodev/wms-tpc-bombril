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
        Schema::create('_tb_pedidos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('numero_pedido', 50);
            $table->integer('unidade_id')->index();
            $table->enum('status', ['pendente', 'em_separacao', 'concluido', 'cancelado'])->nullable()->default('pendente');
            $table->integer('criado_por')->nullable()->index();
            $table->timestamp('data_criacao')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_pedidos');
    }
};
