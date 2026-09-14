<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Services\ComplianceScoreService;
use Illuminate\Http\Request;

class ComplianceScoreController extends Controller
{
    public function __construct(private ComplianceScoreService $complianceScoreService) {}

    public function index(Request $request)
    {
        $query = Opd::with(['websites.latestComplianceScore']);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $opds = $query->orderBy('nama_opd')->get();

        // Diratakan jadi 1 baris per website (bukan per OPD) supaya bisa diranking langsung
        // berdasarkan skor tiap website, sesuai kebutuhan fitur ranking di dokumentasi.
        $rows = $opds->flatMap(function ($opd) {
            return $opd->websites->map(fn($website) => [
                'opd' => $opd,
                'website' => $website,
                'score' => $website->latestComplianceScore,
            ]);
        });

        $sort = $request->get('sort', 'terendah');

        $rows = $rows->sortBy(
            fn($row) => $row['score']->total_skor ?? -1,
            SORT_REGULAR,
            $sort === 'tertinggi'
        )->values();

        return view('compliance-score.index', [
            'rows' => $rows,
            'sort' => $sort,
        ]);
    }

    public function recalculate()
    {
        $count = $this->complianceScoreService->calculateAll();

        return redirect()->route('compliance-score.index')
            ->with('success', "Skor compliance berhasil dihitung ulang untuk {$count} website.");
    }

    public function export(Request $request)
    {
        $kategoriOpd = $request->get('kategori');
        $sort = $request->get('sort', 'terendah');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ComplianceScoreExport($kategoriOpd, $sort),
            'laporan-compliance-score-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
