<?php

namespace App\Providers;

use App\Http\Controllers\SslWarningController;
use App\Models\MonitoringLog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Karena project ini memakai Bootstrap (bukan Tailwind) untuk styling,
        // paginasi (tombol "Next"/"Previous") juga dirender dengan style Bootstrap
        // supaya konsisten dan ikon panahnya tidak raksasa tak terkontrol.
        Paginator::useBootstrapFive();

        // Notifikasi lencana di sidebar (layouts/app.blade.php).
        // Lencana menghitung log pengecekan bermasalah yang LEBIH BARU daripada
        // log terakhir yang sudah dilihat pengguna. Setiap pengecekan baru
        // (tombol "Cek Sekarang" atau jadwal otomatis) menghasilkan log baru,
        // sehingga lencana muncul lagi jika masalahnya masih ditemukan.
        // Saat pengguna membuka menu terkait, lencana langsung hilang.
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            $latestLogIds = fn() => MonitoringLog::selectRaw('MAX(id)')->groupBy('website_id');

            // ID log (pengecekan terbaru per website) yang bermasalah, per jenis notifikasi
            $problems = [];

            // 1) Website down
            $problems['down'] = MonitoringLog::whereIn('id', $latestLogIds())
                ->where('is_up', false)
                ->pluck('id')
                ->all();

            // 2) & 3) hanya untuk admin (menunya hanya tampil untuk admin)
            if ($user->role === 'admin') {
                $problems['ssl'] = SslWarningController::latestSslWarningsQueryStatic()
                    ->pluck('id')
                    ->all();

                $problems['suspicious'] = MonitoringLog::whereIn('id', $latestLogIds())
                    ->where('has_suspicious_content', true)
                    ->pluck('id')
                    ->all();
            }

            // Menu yang "menghapus" lencananya saat dibuka
            $routes = [
                'down'       => 'monitoring.index',
                'ssl'        => 'ssl-warnings.index',
                'suspicious' => 'suspicious-content-report.index',
            ];

            $tableReady = Schema::hasTable('notification_reads');

            // Kolom seen_ids sekarang menyimpan satu angka: ID log terakhir yang sudah dilihat
            $lastSeenRows = $tableReady
                ? DB::table('notification_reads')->where('user_id', $user->id)->pluck('seen_ids', 'key')
                : collect();

            $counts = ['down' => 0, 'ssl' => 0, 'suspicious' => 0];

            foreach ($problems as $key => $ids) {
                // Sedang membuka menu tersebut -> tandai semua log sampai sekarang sudah dilihat
                if ($tableReady && request()->routeIs($routes[$key])) {
                    DB::table('notification_reads')->updateOrInsert(
                        ['user_id' => $user->id, 'key' => $key],
                        ['seen_ids' => json_encode((int) MonitoringLog::max('id')), 'updated_at' => now()]
                    );
                    $counts[$key] = 0;
                    continue;
                }

                $lastSeen = json_decode($lastSeenRows[$key] ?? '0', true);
                $lastSeen = is_numeric($lastSeen) ? (int) $lastSeen : 0; // data lama (array) dianggap belum dilihat

                $counts[$key] = count(array_filter($ids, fn($id) => $id > $lastSeen));
            }

            $view->with('notifCounts', $counts);
        });
    }
}
