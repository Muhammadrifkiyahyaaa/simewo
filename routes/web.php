<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\SslWarningController;
use App\Http\Controllers\SuspiciousKeywordController;
use App\Http\Controllers\SuspiciousContentReportController;
use App\Http\Controllers\OpdSummaryReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ProfileController;
use App\Models\Website;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceScoreController;

Route::get('/', function () {
    return redirect()->route('opd.index');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        $totalWebsite = Website::count();

        $totalUp = Website::whereHas('monitoringLogs', function ($query) {
            $query->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')->from('monitoring_logs')->groupBy('website_id');
            })->where('is_up', true);
        })->count();

        $totalDown = $totalWebsite - $totalUp;

        $websites = Website::with(['opd', 'monitoringLogs' => function ($query) {
            $query->latest('checked_at')->limit(1);
        }])->get();

        $sslWarningCount = SslWarningController::latestSslWarningsQueryStatic()->count();

        $suspiciousCount = Website::whereHas('monitoringLogs', function ($query) {
            $query->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')->from('monitoring_logs')->groupBy('website_id');
            })->where('has_suspicious_content', true);
        })->count();

        return view('dashboard', compact('totalWebsite', 'totalUp', 'totalDown', 'websites', 'sslWarningCount', 'suspiciousCount'));
    })->name('dashboard');

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
