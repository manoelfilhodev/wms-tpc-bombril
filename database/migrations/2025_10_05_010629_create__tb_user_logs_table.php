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
        Schema::create('_tb_user_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('usuario_id')->index();
            $table->integer('unidade_id')->index();
            $table->string('acao')->nullable();
            $table->text('dados')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('navegador')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_user_logs');
    }
};
