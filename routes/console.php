<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan pengecekan otomatis tiap jam, mulai 22:00 sampai 23:59.
// Jadi akan jalan 2x tiap malam: jam 22:00 dan jam 23:00.
Schedule::command('monitoring:run')
    ->hourly()
    ->between('22:00', '23:59');
