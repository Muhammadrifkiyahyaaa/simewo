<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $websites = Website::with([
            'opd',
            'monitoringLogs' => function ($query) {
                $query->latest('checked_at')->limit(1);
            },
            'latestComplianceScore',
        ])->get();

        // Pengecekan TERBARU untuk tiap website
        $latestLogs = MonitoringLog::with('website.opd')
            ->whereIn('id', MonitoringLog::selectRaw('MAX(id)')->groupBy('website_id'))
            ->get();
        $latestByWebsite = $latestLogs->keyBy('website_id');

        // ---------- Angka dasar (dipakai alert di atas) ----------
        $totalWebsite = $websites->count();
        $totalUp = $latestLogs->where('is_up', true)->count();
        $totalDown = $totalWebsite - $totalUp;
        $suspiciousCount = $latestLogs->where('has_suspicious_content', true)->count();
        $sslWarningCount = SslWarningController::latestSslWarningsQueryStatic()->count();

        // ---------- A. Kartu angka berkonteks ----------
        $uptimePct = $totalWebsite > 0 ? round($totalUp / $totalWebsite * 100, 1) : 0;

        $responses = $latestLogs->whereNotNull('response_time_ms')->pluck('response_time_ms');
        $avgResponse = $responses->isNotEmpty() ? $responses->avg() : null;
        $avgResponseLabel = is_null($avgResponse)
            ? '-'
            : ($avgResponse >= 1000
                ? number_format($avgResponse / 1000, 1, ',', '.') . ' dtk'
                : round($avgResponse) . ' ms');

        $nearestSsl = $latestLogs
            ->filter(fn($log) => $log->ssl_valid && $log->ssl_expires_at)
            ->sortBy('ssl_expires_at')
            ->first();
        $sslDays = $nearestSsl
            ? (int) floor(now()->startOfDay()->diffInDays($nearestSsl->ssl_expires_at->copy()->startOfDay(), false))
            : null;

        $scores = $websites->pluck('latestComplianceScore')->filter();
        $avgScore = $scores->isNotEmpty() ? round($scores->avg('total_skor'), 1) : null;
        $belowFullScore = $scores->where('total_skor', '<', 100)->count();

        // ---------- B. Donat kesehatan website ----------
        $threshold = now()->addDays(SslWarningController::THRESHOLD_DAYS);
        $health = [
            'Sehat' => 0,
            'SSL perlu perhatian' => 0,
            'Konten mencurigakan' => 0,
            'Down' => 0,
            'Belum dicek' => 0,
        ];

        foreach ($websites as $website) {
            $log = $latestByWebsite->get($website->id);

            if (!$log) {
                $health['Belum dicek']++;
            } elseif (!$log->is_up) {
                $health['Down']++;
            } elseif ($log->has_suspicious_content) {
                $health['Konten mencurigakan']++;
            } elseif ($log->ssl_valid === false || ($log->ssl_expires_at && $log->ssl_expires_at->lte($threshold))) {
                $health['SSL perlu perhatian']++;
            } else {
                $health['Sehat']++;
            }
        }

        $healthColors = [
            'Sehat' => '#16A34A',
            'SSL perlu perhatian' => '#F59E0B',
            'Konten mencurigakan' => '#DC2626',
            'Down' => '#1E3A5F',
            'Belum dicek' => '#94A3B8',
        ];
        $healthChart = ['labels' => [], 'values' => [], 'colors' => []];
        foreach ($health as $label => $count) {
            if ($count > 0) {
                $healthChart['labels'][] = $label;
                $healthChart['values'][] = $count;
                $healthChart['colors'][] = $healthColors[$label];
            }
        }

        // ---------- C. Tren 10 pengecekan terakhir (per batch) ----------
        $batches = MonitoringLog::selectRaw(
            'batch_id, MIN(checked_at) as started_at, AVG(response_time_ms) as avg_rt, COUNT(*) as total, SUM(CASE WHEN is_up = 1 THEN 1 ELSE 0 END) as up_count'
        )
            ->whereNotNull('batch_id')
            ->groupBy('batch_id')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $trend = [
            'labels' => $batches->map(fn($b) => Carbon::parse($b->started_at)->format('d/m H:i'))->all(),
            'avg' => $batches->map(fn($b) => $b->avg_rt !== null ? (int) round($b->avg_rt) : null)->all(),
            'uptime' => $batches->map(fn($b) => $b->total > 0 ? round($b->up_count / $b->total * 100, 1) : 0)->all(),
            'up' => $batches->map(fn($b) => (int) $b->up_count)->all(),
            'total' => $batches->map(fn($b) => (int) $b->total)->all(),
        ];

        // ---------- D. 5 website dengan respons terlambat ----------
        $slowest = $latestLogs
            ->whereNotNull('response_time_ms')
            ->sortByDesc('response_time_ms')
            ->take(5)
            ->values();

        $slowChart = [
            'labels' => $slowest->map(fn($l) => Str::limit($l->website->opd->nama_opd, 34))->all(),
            'values' => $slowest->pluck('response_time_ms')->map(fn($v) => (int) $v)->all(),
            'urls' => $slowest->map(fn($l) => $l->website->url)->all(),
        ];

        return view('dashboard', compact(
            'websites',
            'totalWebsite',
            'totalUp',
            'totalDown',
            'suspiciousCount',
            'sslWarningCount',
            'uptimePct',
            'avgResponseLabel',
            'nearestSsl',
            'sslDays',
            'avgScore',
            'belowFullScore',
            'healthChart',
            'trend',
            'slowChart'
        ));
    }
}
