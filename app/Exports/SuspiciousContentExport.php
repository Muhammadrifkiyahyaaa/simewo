<?php

namespace App\Exports;

use App\Models\MonitoringLog;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuspiciousContentExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private string $filterType = 'all',
        private ?string $date = null,
        private ?string $month = null
    ) {}

    public function collection(): Enumerable
    {
        $query = MonitoringLog::with('website.opd')
            ->where('has_suspicious_content', true)
            ->latest('checked_at');

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
            'Waktu Terdeteksi',
            'Nama OPD',
            'URL Website',
            'Kata Kunci Terdeteksi',
            'PIC OPD',
            'Kontak PIC',
        ];
    }

    public function map($log): array
    {
        return [
            $log->checked_at->format('d-m-Y H:i'),
            $log->website->opd->nama_opd,
            $log->website->url,
            $log->suspicious_keywords ?? '-',
            $log->website->opd->pic_name,
            $log->website->opd->pic_kontak,
        ];
    }
}
