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
        Schema::create('_tb_produtividade_operador', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('usuario_id')->index();
            $table->integer('unidade_id')->index();
            $table->string('setor', 100)->nullable();
            $table->integer('tarefas_executadas')->nullable()->default(0);
            $table->date('data_registro')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_produtividade_operador');
    }
};
