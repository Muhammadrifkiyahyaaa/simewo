<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opds', function (Blueprint $table) {
            $table->id();
            $table->string('nama_opd');
            $table->enum('kategori', ['Dinas', 'Badan', 'Kecamatan', 'Sekretariat']);
            $table->string('pic_name');
            $table->string('pic_kontak');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opds');
    }
};
