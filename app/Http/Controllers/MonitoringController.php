<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use App\Services\WebsiteMonitorService;
use Illuminate\Http\Request;
use App\Services\ComplianceScoreService;

class MonitoringController extends Controller
{
    public function __construct(
        private WebsiteMonitorService $monitor,
        private ComplianceScoreService $complianceScoreService,
    ) {}

    public function index(Request $request)
    {
        $batchId = $request->get('batch');

        if ($batchId) {
            $logs = MonitoringLog::with('website.opd')
                ->where('batch_id', $batchId)
                ->latest('checked_at')
                ->get();

            return view('monitoring.index', [
                'logs' => $logs,
                'filterType' => 'all',
                'date' => null,
                'month' => null,
                'suspiciousOnly' => false,
                'batchId' => $batchId,
            ]);
        }

        $filterType = $request->get('filter_type', 'all');
        $date = $request->get('date');
        $month = $request->get('month');
        $suspiciousOnly = $request->boolean('suspicious');

        $query = MonitoringLog::with('website.opd')->latest('checked_at');
        $this->applyFilter($query, $filterType, $date, $month);

        if ($suspiciousOnly) {
            $query->where('has_suspicious_content', true);
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('monitoring.index', compact('logs', 'filterType', 'date', 'month', 'suspiciousOnly'));
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

    public function runCheck()
    {
        $count = $this->monitor->checkAllWebsites();
        $batchId = $this->monitor->getLastBatchId();
        $this->complianceScoreService->calculateAll();

        return redirect()->route('monitoring.index', ['batch' => $batchId])
            ->with('success', 'Pengecekan selesai untuk ' . $count . ' website.');
    }

    public function export(Request $request)
    {
        $filterType = $request->get('filter_type', 'all');
        $date = $request->get('date');
        $month = $request->get('month');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonitoringExport($filterType, $date, $month),
            'laporan-monitoring-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
