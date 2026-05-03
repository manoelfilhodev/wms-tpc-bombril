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
        Schema::create('_tb_inventario_ciclico', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('cod_requisicao', 20)->nullable()->unique();
            $table->date('data_requisicao')->nullable();
            $table->enum('status', ['aberta', 'contando', 'contado', 'concluida'])->nullable()->default('aberta');
            $table->string('usuario_criador', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_inventario_ciclico');
    }
};
