<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('_tb_separadores')) {
            return;
        }

        Schema::create('_tb_separadores', function (Blueprint $table) {
            $table->id();
            $table->string('chapa', 50)->unique();
            $table->string('nome', 150);
            $table->string('cargo', 100)->nullable();
            $table->string('turno', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_separadores');
    }
};
