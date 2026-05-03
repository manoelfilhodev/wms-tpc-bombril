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
        Schema::create('_tb_containers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('numero_nf', 50);
            $table->integer('qtd_skus');
            $table->date('data_chegada');
            $table->integer('usuario_id')->nullable()->index();
            $table->integer('unidade_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_containers');
    }
};
