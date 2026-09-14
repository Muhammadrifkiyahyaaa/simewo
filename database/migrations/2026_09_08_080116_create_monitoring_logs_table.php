<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('websites')->onDelete('cascade');
            $table->integer('status_code')->nullable();
            $table->boolean('is_up')->default(false);
            $table->integer('response_time_ms')->nullable();
            $table->boolean('ssl_valid')->nullable();
            $table->timestamp('ssl_expires_at')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
