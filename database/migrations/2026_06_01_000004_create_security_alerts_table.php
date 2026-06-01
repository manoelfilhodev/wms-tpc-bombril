<?php

use App\Enums\AuditSeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 80)->index();
            $table->string('severity', 20)->default(AuditSeverity::WARNING->value)->index();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->uuid('request_id')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
