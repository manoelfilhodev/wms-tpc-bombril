<?php

use App\Enums\AuditSeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('severity', 20)->default(AuditSeverity::INFO->value)->after('module')->index();
            $table->uuid('correlation_id')->nullable()->after('severity')->index();
            $table->uuid('request_id')->nullable()->after('correlation_id')->index();
            $table->unsignedSmallInteger('response_status')->nullable()->after('method')->index();
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'severity',
                'correlation_id',
                'request_id',
                'response_status',
            ]);
        });
    }
};
