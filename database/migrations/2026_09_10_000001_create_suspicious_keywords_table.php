<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suspicious_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique();
            $table->timestamps();
        });

        // Isi awal, dipindah dari yang sebelumnya hardcode di WebsiteMonitorService.
        $defaultKeywords = [
            'slot gacor',
            'slot88',
            'situs slot',
            'daftar slot',
            'link slot',
            'rtp slot',
            'rtp live',
            'maxwin',
            'judi online',
            'togel',
            'bandar togel',
            'agen judi',
            'casino online',
            'live casino',
            'poker online',
            'sbobet',
            'astra33',
            'logo bigwin138',
        ];

        $now = now();

        DB::table('suspicious_keywords')->insert(
            array_map(fn($keyword) => [
                'keyword' => $keyword,
                'created_at' => $now,
                'updated_at' => $now,
            ], $defaultKeywords)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('suspicious_keywords');
    }
};
