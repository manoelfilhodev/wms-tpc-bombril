<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name', 150)->nullable();
            $table->string('user_email', 150)->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('module', 80)->index();
            $table->string('action', 120)->index();
            $table->text('description');
            $table->string('entity_type', 120)->nullable();
            $table->string('entity_id', 120)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_id', 120)->nullable();
            $table->string('route', 255)->nullable();
            $table->string('method', 12)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_system_logs');
    }
};
