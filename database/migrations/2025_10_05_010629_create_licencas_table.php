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
        Schema::create('licencas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('chave', 100)->unique();
            $table->string('cliente');
            $table->dateTime('validade');
            $table->enum('status', ['ativo', 'inativo'])->nullable()->default('ativo');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licencas');
    }
};
