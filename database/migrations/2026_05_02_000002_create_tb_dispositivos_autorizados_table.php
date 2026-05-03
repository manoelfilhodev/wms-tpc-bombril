<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_dispositivos_autorizados', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id')->nullable()->index();
            $table->string('nome_dispositivo', 120);
            $table->string('device_id', 100)->unique();
            $table->enum('tipo', ['web', 'app'])->default('web')->index();
            $table->string('perfil_permitido', 50)->nullable()->index();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamp('ultimo_acesso_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_dispositivos_autorizados');
    }
};
