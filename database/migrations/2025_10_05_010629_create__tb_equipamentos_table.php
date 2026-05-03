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
        Schema::create('_tb_equipamentos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 100);
            $table->string('modelo', 100);
            $table->string('patrimonio', 50)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->enum('status', ['ativo', 'manutenção', 'inativo'])->nullable()->default('ativo');
            $table->string('localizacao', 100)->nullable();
            $table->string('responsavel', 100)->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_equipamentos');
    }
};
