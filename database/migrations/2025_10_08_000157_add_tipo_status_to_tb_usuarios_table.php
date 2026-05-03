<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('_tb_usuarios', 'tipo_usuario')) {
                $table->string('tipo_usuario', 30)->default('operador')->after('password');
            }
            if (!Schema::hasColumn('_tb_usuarios', 'status')) {
                $table->enum('status', ['ativo', 'inativo'])->default('ativo')->after('tipo_usuario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_usuarios', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('_tb_usuarios', 'tipo_usuario')) {
                $table->dropColumn('tipo_usuario');
            }
        });
    }
};
