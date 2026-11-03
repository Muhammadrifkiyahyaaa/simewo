<?php

namespace App\Exports;

use App\Models\Opd;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OpdSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private ?string $kategori = null) {}

    public function collection(): Enumerable
    {
        $query = Opd::with(['websites.monitoringLogs' => function ($q) {
            $q->latest('checked_at')->limit(1);
        }]);

        if ($this->kategori) {
            $query->where('kategori', $this->kategori);
        }

        return $query->orderBy('nama_opd')->get();
    }

    public function headings(): array
    {
        return [
            'Nama OPD',
            'Kategori',
            'PIC',
            'Kontak PIC',
            'Jumlah Website',
            'Website Hidup',
            'Website Down',
        ];
    }

    public function map($opd): array
    {
        $totalWebsite = $opd->websites->count();

        $hidup = $opd->websites->filter(function ($website) {
            $lastLog = $website->monitoringLogs->first();
            return $lastLog && $lastLog->is_up;
        })->count();

        // Tetap dihitung agar website yang belum dicek tidak masuk ke "Down"
        $belumDicek = $opd->websites->filter(function ($website) {
            return $website->monitoringLogs->isEmpty();
        })->count();

        $down = $totalWebsite - $hidup - $belumDicek;

        return [
            $opd->nama_opd,
            $opd->kategori,
            $opd->pic_name,
            $opd->pic_kontak,
            $totalWebsite,
            $hidup,
            $down,
        ];
    }
}
