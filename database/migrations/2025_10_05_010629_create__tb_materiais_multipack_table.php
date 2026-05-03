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
        Schema::create('_tb_materiais_multipack', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sku', 100)->unique();
            $table->string('descricao')->nullable();
            $table->integer('fator_embalagem')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_tb_materiais_multipack');
    }
};
