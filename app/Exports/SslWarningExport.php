<?php

namespace App\Exports;

use App\Models\MonitoringLog;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SslWarningExport implements FromCollection, WithHeadings, WithMapping
{
    /** Ambang batas hari, harus sama dengan SslWarningController. */
    private const THRESHOLD_DAYS = 30;
    private const URGENT_DAYS = 7;

    public function collection(): Enumerable
    {
        $latestLogIds = MonitoringLog::selectRaw('MAX(id) as id')->groupBy('website_id');

        return MonitoringLog::with('website.opd')
            ->whereIn('id', $latestLogIds)
            ->where('ssl_valid', true)
            ->whereNotNull('ssl_expires_at')
            ->where('ssl_expires_at', '<=', now()->addDays(self::THRESHOLD_DAYS))
            ->orderBy('ssl_expires_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama OPD',
            'URL Website',
            'Tanggal Kedaluwarsa SSL',
            'Sisa Hari',
            'Status',
        ];
    }

    public function map($log): array
    {
        $sisaHari = (int) floor(now()->startOfDay()->diffInDays($log->ssl_expires_at->copy()->startOfDay(), false));
        $isUrgent = $sisaHari <= self::URGENT_DAYS;

        return [
            $log->website->opd->nama_opd,
            $log->website->url,
            $log->ssl_expires_at->format('d-m-Y'),
            $sisaHari . ' hari lagi',
            $isUrgent ? 'Mendesak' : 'Perlu Perhatian',
        ];
    }
}
