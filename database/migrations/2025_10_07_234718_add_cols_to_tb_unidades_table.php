<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('_tb_unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('_tb_unidades', 'cep')) {
                $table->string('cep', 9)->nullable();
            }

            if (! Schema::hasColumn('_tb_unidades', 'telefone')) {
                $table->string('telefone', 20)->nullable();
            }

            if (! Schema::hasColumn('_tb_unidades', 'email')) {
                $table->string('email', 150)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('_tb_unidades', function (Blueprint $table) {
            $toDrop = [];

            foreach (['cep', 'telefone', 'email'] as $column) {
                if (Schema::hasColumn('_tb_unidades', $column)) {
                    $toDrop[] = $column;
                }
            }

            if ($toDrop !== []) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
