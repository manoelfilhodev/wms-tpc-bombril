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
        Schema::create('_tb_posicoes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo_posicao', 20)->unique();
            $table->string('setor', 50)->nullable()->index();
            $table->integer('unidade_id')->index();
            $table->enum('status', ['ativa', 'inativa'])->nullable()->default('ativa');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['codigo_posicao'], 'idx_posicao_codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_posicoes');
    }
};
