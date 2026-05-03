<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $database = DB::getDatabaseName();

        $hasConstraint = static function (string $constraintName) use ($database): bool {
            return DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', $database)
                ->where('TABLE_NAME', '_tb_user_logs')
                ->where('CONSTRAINT_NAME', $constraintName)
                ->exists();
        };

        $hasInvalidUsuario = DB::table('_tb_user_logs as l')
            ->leftJoin('_tb_usuarios as u', 'u.id_user', '=', 'l.usuario_id')
            ->whereNull('u.id_user')
            ->exists();

        $hasInvalidUnidade = DB::table('_tb_user_logs as l')
            ->leftJoin('_tb_unidades as un', 'un.id', '=', 'l.unidade_id')
            ->whereNull('un.id')
            ->exists();

        Schema::table('_tb_user_logs', function (Blueprint $table) use ($hasConstraint, $hasInvalidUsuario, $hasInvalidUnidade) {
            if (! $hasConstraint('_tb_user_logs_ibfk_1') && ! $hasInvalidUsuario) {
                $table->foreign(['usuario_id'], '_tb_user_logs_ibfk_1')
                    ->references(['id_user'])
                    ->on('_tb_usuarios')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            }

            if (! $hasConstraint('_tb_user_logs_ibfk_2') && ! $hasInvalidUnidade) {
                $table->foreign(['unidade_id'], '_tb_user_logs_ibfk_2')
                    ->references(['id'])
                    ->on('_tb_unidades')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('_tb_user_logs', function (Blueprint $table) {
            if (Schema::hasColumn('_tb_user_logs', 'usuario_id')) {
                try {
                    $table->dropForeign('_tb_user_logs_ibfk_1');
                } catch (\Throwable $exception) {
                }
            }

            if (Schema::hasColumn('_tb_user_logs', 'unidade_id')) {
                try {
                    $table->dropForeign('_tb_user_logs_ibfk_2');
                } catch (\Throwable $exception) {
                }
            }
        });
    }
};
