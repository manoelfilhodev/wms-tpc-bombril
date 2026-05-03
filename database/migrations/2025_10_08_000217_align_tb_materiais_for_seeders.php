<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_materiais', function (Blueprint $table) {
            if (! Schema::hasColumn('_tb_materiais', 'descricao')) {
                $table->text('descricao')->nullable();
            }

            if (! Schema::hasColumn('_tb_materiais', 'sku')) {
                $table->string('sku', 60)->nullable()->unique();
            }

            if (! Schema::hasColumn('_tb_materiais', 'categoria_id')) {
                $table->unsignedBigInteger('categoria_id')->nullable();
            }

            if (! Schema::hasColumn('_tb_materiais', 'unidade_medida')) {
                $table->string('unidade_medida', 10)->nullable();
            }

            if (! Schema::hasColumn('_tb_materiais', 'status')) {
                $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_materiais', function (Blueprint $table) {
            foreach (['status', 'unidade_medida', 'categoria_id', 'sku', 'descricao'] as $column) {
                if (Schema::hasColumn('_tb_materiais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
