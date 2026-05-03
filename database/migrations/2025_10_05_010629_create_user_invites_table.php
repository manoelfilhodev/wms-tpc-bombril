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
        Schema::create('user_invites', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('token', 64)->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('nivel_padrao', 50)->nullable()->default('Expedicao');
            $table->string('unidade_padrao', 100)->nullable();
            $table->dateTime('valido_ate')->nullable();
            $table->boolean('usado')->nullable()->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invites');
    }
};
