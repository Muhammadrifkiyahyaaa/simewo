<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->string('error_type')->nullable()->after('status_code');
            $table->string('error_message')->nullable()->after('error_type');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropColumn(['error_type', 'error_message']);
        });
    }
};
