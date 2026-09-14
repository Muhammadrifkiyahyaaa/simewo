<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->boolean('has_suspicious_content')->default(false)->after('ssl_expires_at');
            $table->string('suspicious_keywords')->nullable()->after('has_suspicious_content');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropColumn(['has_suspicious_content', 'suspicious_keywords']);
        });
    }
};
