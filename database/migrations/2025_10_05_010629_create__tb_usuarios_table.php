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
        Schema::create('_tb_usuarios', function (Blueprint $table) {
            $table->integer('id_user', true);
            $table->string('nome', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('password')->nullable();
            $table->integer('unidade_id')->index();
            $table->enum('tipo', ['admin', 'gestor', 'operador'])->nullable()->default('operador');
            $table->enum('status', ['ativo', 'inativo'])->nullable()->default('ativo');
            $table->string('reset_token')->nullable();
            $table->dateTime('token_expire')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('nivel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_usuarios');
    }
};
