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
        Schema::create('_tb_contagem_ciclica', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sku', 100);
            $table->integer('quantidade');
            $table->integer('usuario_id')->nullable()->index();
            $table->integer('unidade_id')->nullable()->index();
            $table->date('data_contagem');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_contagem_ciclica');
    }
};
