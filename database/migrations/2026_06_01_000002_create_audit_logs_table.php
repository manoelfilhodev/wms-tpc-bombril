<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('action', 80)->index();
            $table->string('module', 80)->nullable()->index();
            $table->string('route', 180)->nullable();
            $table->string('method', 12)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload_resumo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('id_user')->on('_tb_usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
