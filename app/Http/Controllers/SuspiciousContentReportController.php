<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;

class SuspiciousContentReportController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->get('filter_type', 'all');
        $date = $request->get('date');
        $month = $request->get('month');

        $query = MonitoringLog::with('website.opd')
            ->where('has_suspicious_content', true)
            ->latest('checked_at');

        $this->applyFilter($query, $filterType, $date, $month);

        $logs = $query->paginate(15)->withQueryString();

        return view('suspicious-content-report.index', compact('logs', 'filterType', 'date', 'month'));
    }

    private function applyFilter($query, string $filterType, ?string $date, ?string $month): void
    {
        if ($filterType === 'day' && $date) {
            $query->whereDate('checked_at', $date);
        } elseif ($filterType === 'month' && $month) {
            [$year, $mon] = explode('-', $month);
            $query->whereYear('checked_at', $year)->whereMonth('checked_at', $mon);
        }
    }

    public function export(Request $request)
    {
        $filterType = $request->get('filter_type', 'all');
        $date = $request->get('date');
        $month = $request->get('month');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SuspiciousContentExport($filterType, $date, $month),
            'laporan-konten-mencurigakan-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
