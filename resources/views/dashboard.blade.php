@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h5 class="mb-3">Dashboard</h5>

@if ($suspiciousCount > 0)
<a href="{{ route('monitoring.index', ['suspicious' => 1]) }}" class="alert alert-danger d-flex align-items-center justify-content-between mb-3 text-decoration-none text-reset" role="alert">
    <div>
        <i class="bi bi-exclamation-octagon me-2"></i>
        <strong>{{ $suspiciousCount }} website</strong> terdeteksi ada indikasi konten mencurigakan (judi online). Segera periksa manual.
    </div>
    <span class="text-decoration-underline">Lihat riwayat &raquo;</span>
</a>
@endif

@if ($totalDown > 0)
<a href="{{ route('opd.index', ['status' => 'down']) }}" class="alert alert-danger d-flex align-items-center justify-content-between mb-3 text-decoration-none text-reset" role="alert">
    <div>
        <i class="bi bi-wifi-off me-2"></i>
        <strong>{{ $totalDown }} website</strong> saat ini berstatus down (tidak bisa diakses).
    </div>
    <span class="text-decoration-underline">Lihat daftar &raquo;</span>
</a>
@endif

@if ($sslWarningCount > 0)
<a href="{{ route('ssl-warnings.index') }}" class="alert alert-warning d-flex align-items-center justify-content-between mb-4 text-decoration-none text-reset" role="alert">
    <div>
        <i class="bi bi-shield-exclamation me-2"></i>
        <strong>{{ $sslWarningCount }} website</strong> SSL-nya akan kedaluwarsa dalam 30 hari ke depan.
    </div>
    <span class="text-decoration-underline">Lihat detail &raquo;</span>
</a>
@endif

{{-- A. Kartu angka berkonteks --}}
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <div class="text-muted small">Ketersediaan Website</div>
            <div class="fs-3 fw-bold {{ $uptimePct >= 100 ? 'text-success' : 'text-danger' }}">{{ str_replace('.', ',', (string) $uptimePct) }}%</div>
            <div class="text-muted small">{{ $totalUp }} dari {{ $totalWebsite }} website hidup</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <div class="text-muted small">Rata-rata Waktu Respons</div>
            <div class="fs-3 fw-bold">{{ $avgResponseLabel }}</div>
            <div class="text-muted small">Dari pengecekan terbaru tiap website</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <div class="text-muted small">SSL Paling Dekat Kedaluwarsa</div>
            @if ($nearestSsl)
            <div class="fs-3 fw-bold {{ $sslDays <= 30 ? 'text-warning' : '' }}">{{ $nearestSsl->ssl_expires_at->format('d M Y') }}</div>
            <div class="text-muted small">{{ $sslDays }} hari lagi &middot; {{ \Illuminate\Support\Str::limit($nearestSsl->website->opd->nama_opd, 24) }}</div>
            @else
            <div class="fs-3 fw-bold">-</div>
            <div class="text-muted small">Belum ada data SSL</div>
            @endif
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <div class="text-muted small">Rata-rata Skor Compliance</div>
            @if (!is_null($avgScore))
            <div class="fs-3 fw-bold">{{ str_replace('.', ',', (string) $avgScore) }}<span class="fs-6 fw-normal text-muted"> / 100</span></div>
            <div class="text-muted small">
                {{ $belowFullScore > 0 ? $belowFullScore . ' website skor di bawah 100' : 'Semua website skor 100' }}
            </div>
            @else
            <div class="fs-3 fw-bold">-</div>
            <div class="text-muted small">Skor belum dihitung</div>
            @endif
        </div>
    </div>
</div>

{{-- B. Donat kesehatan + C. Tren respons --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <h6 class="mb-3">Kesehatan Website</h6>
            @if (count($healthChart['values']) > 0)
            <div style="position: relative; height: 260px;">
                <canvas id="chartHealth"></canvas>
            </div>
            @else
            <div class="text-muted text-center py-5">Belum ada data website.</div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="bg-white p-3 rounded shadow-sm h-100">
            <h6 class="mb-3">Tren Waktu Respons (10 Pengecekan Terakhir)</h6>
            @if (count($trend['labels']) > 0)
            <div style="position: relative; height: 260px;">
                <canvas id="chartTrend"></canvas>
            </div>
            @else
            <div class="text-muted text-center py-5">Belum ada riwayat pengecekan.</div>
            @endif
        </div>
    </div>
</div>

{{-- D. Website dengan respons terlambat --}}
<div class="bg-white p-3 rounded shadow-sm mb-4">
    <h6 class="mb-3">5 Website dengan Respons Terlambat</h6>
    @if (count($slowChart['values']) > 0)
    <div style="position: relative; height: 230px;">
        <canvas id="chartSlow"></canvas>
    </div>
    @else
    <div class="text-muted text-center py-4">Belum ada data waktu respons.</div>
    @endif
</div>

<div class="bg-white p-3 rounded shadow-sm">
    <h6 class="mb-3">Status Terkini per OPD</h6>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>OPD</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Cek Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($websites as $website)
                @php $lastLog = $website->monitoringLogs->first(); @endphp
                <tr>
                    <td>{{ $website->opd->nama_opd }}</td>
                    <td>{{ $website->url }}</td>
                    <td>
                        @if (!$lastLog)
                        <span class="badge bg-secondary">Belum dicek</span>
                        @elseif ($lastLog->is_up)
                        <span class="badge bg-success">Hidup</span>
                        @else
                        <span class="badge bg-danger">Down</span>
                        @endif
                    </td>
                    <td>{{ $lastLog ? $lastLog->checked_at->diffForHumans() : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada data website.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Segoe UI', Arial, sans-serif";
        Chart.defaults.color = '#64748B';

        const health = @json($healthChart);
        const trend = @json($trend);
        const slow = @json($slowChart);

        // B. Donat kesehatan website
        const healthEl = document.getElementById('chartHealth');
        if (healthEl) {
            new Chart(healthEl, {
                type: 'doughnut',
                data: {
                    labels: health.labels.map(function(label, i) {
                        return label + ' (' + health.values[i] + ')';
                    }),
                    datasets: [{
                        data: health.values,
                        backgroundColor: health.colors,
                        borderColor: '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 14
                            }
                        }
                    }
                }
            });
        }

        // C. Tren waktu respons per pengecekan
        const trendEl = document.getElementById('chartTrend');
        if (trendEl) {
            new Chart(trendEl, {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [{
                        data: trend.avg,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563EB',
                        spanGaps: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return 'Rata-rata respons: ' + ctx.parsed.y + ' ms';
                                },
                                afterLabel: function(ctx) {
                                    const i = ctx.dataIndex;
                                    return 'Ketersediaan: ' + trend.uptime[i] + '% (' + trend.up[i] + '/' + trend.total[i] + ' website hidup)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#EEF2F7'
                            },
                            ticks: {
                                callback: function(v) {
                                    return v + ' ms';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // D. 5 website dengan respons terlambat
        const slowEl = document.getElementById('chartSlow');
        if (slowEl) {
            new Chart(slowEl, {
                type: 'bar',
                data: {
                    labels: slow.labels,
                    datasets: [{
                        data: slow.values,
                        backgroundColor: '#1E3A5F',
                        borderRadius: 4,
                        barThickness: 22
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.parsed.x + ' ms';
                                },
                                afterLabel: function(ctx) {
                                    return slow.urls[ctx.dataIndex];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: '#EEF2F7'
                            },
                            ticks: {
                                callback: function(v) {
                                    return v + ' ms';
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection