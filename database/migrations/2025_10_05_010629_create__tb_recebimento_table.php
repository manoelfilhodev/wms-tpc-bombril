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
        Schema::create('_tb_recebimento', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('unidade_id')->index();
            $table->string('nota_fiscal', 50)->nullable();
            $table->string('fornecedor', 100)->nullable();
            $table->date('data_recebimento')->nullable();
            $table->integer('usuario_id')->nullable()->index();
            $table->enum('status', ['pendente', 'conferido', 'armazenado'])->nullable()->default('pendente');
            $table->timestamp('created_at')->useCurrent();
            $table->string('transportadora', 100)->nullable();
            $table->string('motorista', 100)->nullable();
            $table->string('placa', 20)->nullable();
            $table->string('tipo', 50)->nullable();
            $table->time('horario_janela')->nullable();
            $table->time('horario_chegada')->nullable();
            $table->string('doca', 20)->nullable();
            $table->string('xml_nfe')->nullable();
            $table->string('foto_inicio_veiculo')->nullable();
            $table->string('foto_fim_veiculo')->nullable();
            $table->text('assinatura_conferente')->nullable();
            $table->text('ressalva_assistente')->nullable();
            $table->integer('confirmado_por')->nullable();
            $table->dateTime('data_fechamento')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_recebimento');
    }
};
