<?php

namespace App\Exports;

use App\Models\Opd;
use App\Services\ComplianceScoreService;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplianceScoreExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $kategoriOpd = null,
        private string $sort = 'terendah'
    ) {}

    public function collection(): Enumerable
    {
        $query = Opd::with(['websites.latestComplianceScore']);

        if ($this->kategoriOpd) {
            $query->where('kategori', $this->kategoriOpd);
        }

        $opds = $query->orderBy('nama_opd')->get();

        // Diratakan jadi 1 baris per website, sama seperti di ComplianceScoreController,
        // supaya laporan bisa langsung diranking berdasarkan skor tiap website.
        $rows = $opds->flatMap(function ($opd) {
            return $opd->websites->map(fn($website) => [
                'opd' => $opd,
                'website' => $website,
                'score' => $website->latestComplianceScore,
            ]);
        });

        return $rows->sortBy(
            fn($row) => $row['score']->total_skor ?? -1,
            SORT_REGULAR,
            $this->sort === 'tertinggi'
        )->values();
    }

    public function headings(): array
    {
        return [
            'Nama OPD',
            'Kategori OPD',
            'URL Website',
            'Skor Domain Resmi (maks 20)',
            'Skor SSL Aktif (maks 30)',
            'Skor Uptime (maks 50)',
            'Total Skor',
            'Kategori Kepatuhan',
            'Terakhir Dihitung',
        ];
    }

    public function map($row): array
    {
        $score = $row['score'];

        if (!$score) {
            return [
                $row['opd']->nama_opd,
                $row['opd']->kategori,
                $row['website']->url,
                '-',
                '-',
                '-',
                '-',
                'Belum ada data',
                '-',
            ];
        }

        $kategori = ComplianceScoreService::kategori($score->total_skor);

        return [
            $row['opd']->nama_opd,
            $row['opd']->kategori,
            $row['website']->url,
            $score->skor_domain_resmi,
            $score->skor_ssl_aktif,
            $score->skor_uptime,
            $score->total_skor,
            $kategori['label'],
            $score->calculated_at->format('d-m-Y H:i'),
        ];
    }
}
