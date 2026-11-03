<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\Request;

class OpdSummaryReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Opd::with(['websites.monitoringLogs' => function ($q) {
            $q->latest('checked_at')->limit(1);
        }]);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $opds = $query->orderBy('nama_opd')->get()->map(function ($opd) {
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
                'opd' => $opd,
                'total' => $totalWebsite,
                'hidup' => $hidup,
                'down' => $down,
            ];
        });

        return view('opd-summary-report.index', compact('opds'));
    }

    public function export(Request $request)
    {
        $kategori = $request->get('kategori');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\OpdSummaryExport($kategori),
            'laporan-rekap-opd-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
