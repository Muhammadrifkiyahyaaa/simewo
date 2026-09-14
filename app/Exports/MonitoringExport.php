<?php

namespace App\Exports;

use App\Models\MonitoringLog;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonitoringExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private string $filterType = 'all',
        private ?string $date = null,
        private ?string $month = null
    ) {}

    public function collection(): Enumerable
    {
        $query = MonitoringLog::with('website.opd')->latest('checked_at');

        if ($this->filterType === 'day' && $this->date) {
            $query->whereDate('checked_at', $this->date);
        } elseif ($this->filterType === 'month' && $this->month) {
            [$year, $mon] = explode('-', $this->month);
            $query->whereYear('checked_at', $year)->whereMonth('checked_at', $mon);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Waktu Cek',
            'Nama OPD',
            'URL Website',
            'Status',
            'Kode HTTP',
            'Waktu Respons (ms)',
            'Keterangan Down',
            'SSL Valid',
            'SSL Kedaluwarsa',
        ];
    }

    public function map($log): array
    {
        return [
            $log->checked_at->format('d-m-Y H:i'),
            $log->website->opd->nama_opd,
            $log->website->url,
            $log->is_up ? 'Hidup' : 'Down',
            $log->status_code ?? '-',
            $log->response_time_ms ?? '-',
            $log->is_up ? '-' : ($log->errorTypeLabel() ?? '-'),
            is_null($log->ssl_valid) ? 'N/A' : ($log->ssl_valid ? 'Valid' : 'Invalid'),
            $log->ssl_expires_at ? $log->ssl_expires_at->format('d-m-Y') : '-',
        ];
    }
}
