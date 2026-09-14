<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        $websites = Website::with('opd')->latest()->paginate(10);
        return view('website.index', compact('websites'));
    }

    public function create()
    {
        $opds = Opd::orderBy('nama_opd')->get();
        return view('website.create', compact('opds'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'url' => $this->normalizeUrl($request->url),
        ]);

        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'url' => 'required|url|max:255',
        ]);

        Website::create($validated);

        return redirect()->route('website.index')->with('success', 'Data website berhasil ditambahkan.');
    }

    public function edit(Website $website)
    {
        $opds = Opd::orderBy('nama_opd')->get();
        return view('website.edit', compact('website', 'opds'));
    }

    public function update(Request $request, Website $website)
    {
        $request->merge([
            'url' => $this->normalizeUrl($request->url),
        ]);

        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'url' => 'required|url|max:255',
        ]);

        $website->update($validated);

        return redirect()->route('website.index')->with('success', 'Data website berhasil diperbarui.');
    }

    public function destroy(Website $website)
    {
        $website->delete();

        return redirect()->route('website.index')->with('success', 'Data website berhasil dihapus.');
    }

    public function detail(Request $request, Website $website)
    {
        $period = $request->get('period', '7');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$rangeStart, $rangeEnd] = $this->resolveDateRange($period, $startDate, $endDate);

        $baseQuery = fn() => $website->monitoringLogs()
            ->whereBetween('checked_at', [$rangeStart, $rangeEnd]);

        $totalChecks = $baseQuery()->count();
        $upChecks = $baseQuery()->where('is_up', true)->count();
        $downChecks = $totalChecks - $upChecks;
        $uptimePercentage = $totalChecks > 0 ? round(($upChecks / $totalChecks) * 100, 1) : null;
        $avgResponseTime = $baseQuery()->where('is_up', true)->avg('response_time_ms');

        $latestLog = $website->monitoringLogs()->latest('checked_at')->first();

        $logs = $baseQuery()->latest('checked_at')->paginate(15)->withQueryString();

        return view('website.detail', [
            'website' => $website,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'totalChecks' => $totalChecks,
            'upChecks' => $upChecks,
            'downChecks' => $downChecks,
            'uptimePercentage' => $uptimePercentage,
            'avgResponseTime' => $avgResponseTime,
            'latestLog' => $latestLog,
            'logs' => $logs,
        ]);
    }

    /**
     * Menentukan rentang tanggal berdasarkan pilihan periode di halaman detail.
     * Default: 7 hari terakhir.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function resolveDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        if ($period === 'custom' && $startDate && $endDate) {
            return [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $days = $period === '30' ? 30 : 7;

        return [now()->subDays($days)->startOfDay(), now()->endOfDay()];
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        if (!preg_match('~^https?://~i', $url)) {
            return 'https://' . $url;
        }

        return $url;
    }
}
