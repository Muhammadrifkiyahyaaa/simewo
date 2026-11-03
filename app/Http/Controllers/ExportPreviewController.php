<?php

namespace App\Http\Controllers;

use App\Exports\ComplianceScoreExport;
use App\Exports\MonitoringExport;
use App\Exports\OpdSummaryExport;
use App\Exports\SslWarningExport;
use App\Exports\SuspiciousContentExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportPreviewController extends Controller
{
    /** Jumlah baris maksimal yang ditampilkan di preview. */
    private const PREVIEW_LIMIT = 50;

    /**
     * Daftar laporan yang bisa dipreview/diunduh sebagai PDF.
     * Kunci = potongan URL laporan, contoh: /monitoring/export -> "monitoring".
     */
    private function config(string $report, Request $request): array
    {
        $filterType = $request->get('filter_type', 'all');
        $date = $request->get('date');
        $month = $request->get('month');
        $kategori = $request->get('kategori');
        $sort = $request->get('sort', 'terendah');

        $reports = [
            'monitoring' => [
                'title' => 'Riwayat Monitoring',
                'file' => 'riwayat-monitoring',
                'export' => fn() => new MonitoringExport($filterType, $date, $month),
            ],
            'peringatan-ssl' => [
                'title' => 'Peringatan SSL',
                'file' => 'peringatan-ssl',
                'export' => fn() => new SslWarningExport(),
            ],
            'laporan-konten-mencurigakan' => [
                'title' => 'Laporan Konten Mencurigakan',
                'file' => 'laporan-konten-mencurigakan',
                'export' => fn() => new SuspiciousContentExport($filterType, $date, $month),
            ],
            'laporan-rekap-opd' => [
                'title' => 'Laporan Rekap OPD',
                'file' => 'laporan-rekap-opd',
                'export' => fn() => new OpdSummaryExport($kategori),
            ],
            'compliance-score' => [
                'title' => 'Compliance Score',
                'file' => 'compliance-score',
                'export' => fn() => new ComplianceScoreExport($kategori, $sort),
            ],
        ];

        abort_unless(isset($reports[$report]), 404);

        return $reports[$report];
    }

    /**
     * Susun judul, header, dan baris data memakai class Export yang sama
     * dengan file Excel, sehingga isi preview/PDF identik dengan Excel.
     */
    private function build(string $report, Request $request, ?int $limit = null): array
    {
        $config = $this->config($report, $request);
        $export = ($config['export'])();

        $collection = $export->collection();
        $total = $collection->count();

        if ($limit) {
            $collection = $collection->take($limit);
        }

        $rows = $collection->map(fn($item) => $export->map($item))->values()->all();

        return [
            'title' => $config['title'],
            'file' => $config['file'],
            'headings' => $export->headings(),
            'rows' => $rows,
            'total' => $total,
        ];
    }

    public function preview(Request $request, string $report)
    {
        $data = $this->build($report, $request, self::PREVIEW_LIMIT);

        return view('export.preview-table', $data);
    }

    public function pdf(Request $request, string $report)
    {
        set_time_limit(120);

        $data = $this->build($report, $request);
        $data['printedAt'] = now()->format('d-m-Y H:i');

        return Pdf::loadView('export.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($data['file'] . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
