<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;

class SslWarningController extends Controller
{
    /**
     * Ambang batas hari sebelum SSL kedaluwarsa yang dianggap "peringatan".
     */
    const THRESHOLD_DAYS = 30;

    /**
     * Ambang batas hari untuk kategori "mendesak" (ditandai merah).
     */
    const URGENT_DAYS = 7;

    public function index()
    {
        $logs = $this->latestSslWarningsQuery()->get();

        return view('ssl-warnings.index', [
            'logs' => $logs,
            'thresholdDays' => self::THRESHOLD_DAYS,
            'urgentDays' => self::URGENT_DAYS,
        ]);
    }

    /**
     * Query bersama: ambil log monitoring TERBARU per website yang SSL-nya
     * masih valid tapi akan kedaluwarsa dalam waktu dekat, urut paling mendesak dulu.
     * Dipakai juga oleh dashboard untuk menghitung jumlah peringatan.
     */
    public static function latestSslWarningsQueryStatic()
    {
        return (new self())->latestSslWarningsQuery();
    }

    private function latestSslWarningsQuery()
    {
        $latestLogIds = MonitoringLog::selectRaw('MAX(id) as id')->groupBy('website_id');

        return MonitoringLog::with('website.opd')
            ->whereIn('id', $latestLogIds)
            ->where('ssl_valid', true)
            ->whereNotNull('ssl_expires_at')
            ->where('ssl_expires_at', '<=', now()->addDays(self::THRESHOLD_DAYS))
            ->orderBy('ssl_expires_at', 'asc');
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SslWarningExport(),
            'laporan-peringatan-ssl-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
