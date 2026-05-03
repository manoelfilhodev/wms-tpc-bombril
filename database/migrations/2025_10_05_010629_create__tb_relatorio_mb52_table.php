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
        Schema::create('_tb_relatorio_mb52', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('data_referencia')->nullable();
            $table->string('centro', 10)->nullable();
            $table->string('material', 50)->nullable();
            $table->string('descricao')->nullable();
            $table->string('deposito', 20)->nullable();
            $table->string('unidade_medida', 10)->nullable();
            $table->decimal('utilizacao_livre', 15, 3)->nullable()->default(0);
            $table->decimal('bloqueado', 15, 3)->nullable()->default(0);
            $table->decimal('controle_qualidade', 15, 3)->nullable()->default(0);
            $table->decimal('transito_te', 15, 3)->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_relatorio_mb52');
    }
};
