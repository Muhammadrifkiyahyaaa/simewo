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

<div class="row mb-4">
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Total Website</div>
            <div class="fs-3 fw-bold">{{ $totalWebsite }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Website Hidup</div>
            <div class="fs-3 fw-bold text-success">{{ $totalUp }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Website Down</div>
            <div class="fs-3 fw-bold text-danger">{{ $totalDown }}</div>
        </div>
    </div>
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
@endsection