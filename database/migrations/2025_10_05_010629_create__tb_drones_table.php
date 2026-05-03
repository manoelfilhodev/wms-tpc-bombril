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
        Schema::create('_tb_drones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tag')->unique();
            $table->string('bearer_token');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamp('ultimo_envio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_drones');
    }
};
