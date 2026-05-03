<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('_tb_usuarios', 'unidade_id')) {
                $table->integer('unidade_id')->nullable();
            }
        });

        $unidadeId = DB::table('_tb_unidades')->where('nome', 'Unidade Central')->value('id');

        if (! $unidadeId) {
            $unidadeId = DB::table('_tb_unidades')->min('id');
        }

        if ($unidadeId) {
            DB::table('_tb_usuarios')->whereNull('unidade_id')->update(['unidade_id' => $unidadeId]);

            try {
                DB::statement('ALTER TABLE `_tb_usuarios` MODIFY `unidade_id` INT NOT NULL');
            } catch (\Throwable $exception) {
            }
        }

        if ($isSqlite) {
            return;
        }

        try {
            DB::statement('ALTER TABLE `_tb_usuarios` DROP FOREIGN KEY `_tb_usuarios_ibfk_1`');
        } catch (\Throwable $exception) {
        }

        $hasFk = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', '_tb_usuarios')
            ->where('CONSTRAINT_NAME', 'fk_usuarios_unidade')
            ->exists();

        if (! $hasFk) {
            try {
                DB::statement('ALTER TABLE `_tb_usuarios` ADD CONSTRAINT `fk_usuarios_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `_tb_unidades`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
            } catch (\Throwable $exception) {
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('_tb_usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_usuarios', 'unidade_id')) {
                $table->dropColumn('unidade_id');
            }
        });
    }
};
