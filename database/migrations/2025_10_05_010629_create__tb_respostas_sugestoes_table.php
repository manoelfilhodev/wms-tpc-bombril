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
        Schema::create('_tb_respostas_sugestoes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sugestao_id')->index();
            $table->text('resposta');
            $table->enum('status', ['pendente', 'em_andamento', 'concluida', 'recusada']);
            $table->integer('respondido_por')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_respostas_sugestoes');
    }
};
