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
        Schema::create('_tb_unidades', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nome', 100);
            $table->string('cnpj', 18)->nullable();
            $table->text('endereco')->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->enum('status', ['ativo', 'inativo'])->nullable()->default('ativo');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_unidades');
    }
};
