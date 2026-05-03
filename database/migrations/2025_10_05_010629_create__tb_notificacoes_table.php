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
        Schema::create('_tb_notificacoes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('titulo');
            $table->text('mensagem')->nullable();
            $table->enum('status', ['pendente', 'visualizada', 'resolvida'])->nullable()->default('pendente');
            $table->enum('tipo', ['info', 'alerta', 'urgente'])->nullable()->default('info');
            $table->integer('destino_unidade_id')->nullable()->index();
            $table->integer('destino_usuario_id')->nullable()->index();
            $table->boolean('lida')->nullable()->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_notificacoes');
    }
};
