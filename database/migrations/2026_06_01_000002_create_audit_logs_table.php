<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable()->index();
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

            return;
        }

        DB::statement('ALTER TABLE `audit_logs` MODIFY `user_id` INT NULL');

        if (! $this->indexExists('audit_logs', 'audit_logs_user_id_index')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        if (! $this->foreignKeyExists('audit_logs', 'audit_logs_user_id_foreign')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreign('user_id')->references('id_user')->on('_tb_usuarios')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};
