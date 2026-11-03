<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportPreviewController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\SslWarningController;
use App\Http\Controllers\SuspiciousKeywordController;
use App\Http\Controllers\SuspiciousContentReportController;
use App\Http\Controllers\OpdSummaryReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceScoreController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Semua user login (admin & staf) hanya bisa melihat
    Route::resource('opd', OpdController::class)->only(['index']);
    Route::resource('website', WebsiteController::class)->only(['index']);
    Route::get('/website/{website}/detail', [WebsiteController::class, 'detail'])->name('website.detail');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/export', [MonitoringController::class, 'export'])->name('monitoring.export');

    Route::get('/peringatan-ssl', [SslWarningController::class, 'index'])->name('ssl-warnings.index');
    Route::get('/peringatan-ssl/export', [SslWarningController::class, 'export'])->name('ssl-warnings.export');

    Route::get('/kata-diblokir', [SuspiciousKeywordController::class, 'index'])->name('suspicious-keywords.index');

    Route::get('/laporan-konten-mencurigakan', [SuspiciousContentReportController::class, 'index'])->name('suspicious-content-report.index');
    Route::get('/laporan-konten-mencurigakan/export', [SuspiciousContentReportController::class, 'export'])->name('suspicious-content-report.export');

    Route::get('/laporan-rekap-opd', [OpdSummaryReportController::class, 'index'])->name('opd-summary-report.index');
    Route::get('/laporan-rekap-opd/export', [OpdSummaryReportController::class, 'export'])->name('opd-summary-report.export');

    Route::get('/compliance-score', [ComplianceScoreController::class, 'index'])->name('compliance-score.index');
    Route::get('/compliance-score/export', [ComplianceScoreController::class, 'export'])->name('compliance-score.export');

    // Preview laporan sebelum diunduh + unduh versi PDF
    Route::get('/export-preview/{report}', [ExportPreviewController::class, 'preview'])->name('export.preview');
    Route::get('/export-pdf/{report}', [ExportPreviewController::class, 'pdf'])->name('export.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Hanya admin: kelola data & akun pengguna
    Route::middleware('admin')->group(function () {
        Route::resource('opd', OpdController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('website', WebsiteController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('/monitoring/run', [MonitoringController::class, 'runCheck'])->name('monitoring.run');
        Route::post('/kata-diblokir', [SuspiciousKeywordController::class, 'store'])->name('suspicious-keywords.store');
        Route::delete('/kata-diblokir/{suspiciousKeyword}', [SuspiciousKeywordController::class, 'destroy'])->name('suspicious-keywords.destroy');
        Route::post('/compliance-score/hitung-ulang', [ComplianceScoreController::class, 'recalculate'])->name('compliance-score.recalculate');
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__ . '/auth.php';
