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

        $hasConstraint = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', '_tb_usuarios')
            ->where('CONSTRAINT_NAME', '_tb_usuarios_ibfk_1')
            ->exists();

        $hasInvalidUnidade = DB::table('_tb_usuarios as u')
            ->leftJoin('_tb_unidades as un', 'un.id', '=', 'u.unidade_id')
            ->whereNull('un.id')
            ->exists();

        Schema::table('_tb_usuarios', function (Blueprint $table) use ($hasConstraint, $hasInvalidUnidade) {
            if (! $hasConstraint && ! $hasInvalidUnidade) {
                $table->foreign(['unidade_id'], '_tb_usuarios_ibfk_1')
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

        Schema::table('_tb_usuarios', function (Blueprint $table) {
            try {
                $table->dropForeign('_tb_usuarios_ibfk_1');
            } catch (\Throwable $exception) {
            }
        });
    }
};
