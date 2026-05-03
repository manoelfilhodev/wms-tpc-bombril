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
        Schema::create('_tb_posicoes_ocupadas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('posicao_id')->unique();
            $table->integer('usuario_id');
            $table->dateTime('ocupada_em')->nullable()->useCurrent();
            $table->dateTime('expiracao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_posicoes_ocupadas');
    }
};
