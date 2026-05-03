<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_tb_apontamentos_paletes_stretch', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('palete_codigo', 120);
            $table->integer('usuario_id')->nullable()->index();
            $table->integer('unidade_id')->nullable()->index();
            $table->enum('status', ['APONTADO', 'CANCELADO'])->default('APONTADO');
            $table->enum('origem', ['APP', 'WEB', 'API'])->default('WEB');
            $table->text('observacao')->nullable();
            $table->uuid('client_uuid')->nullable()->unique();
            $table->string('device_id', 120)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->dateTime('apontado_em_app')->nullable();
            $table->dateTime('apontado_em_servidor')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('palete_codigo', 'idx_stretch_palete_codigo');
            $table->index('status', 'idx_stretch_status');
            $table->index('origem', 'idx_stretch_origem');
            $table->index('apontado_em_servidor', 'idx_stretch_apontado_em_servidor');

            $table->foreign('usuario_id', 'fk_stretch_usuario')
                ->references('id_user')
                ->on('_tb_usuarios')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->foreign('unidade_id', 'fk_stretch_unidade')
                ->references('id')
                ->on('_tb_unidades')
                ->nullOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_tb_apontamentos_paletes_stretch');
    }
};
