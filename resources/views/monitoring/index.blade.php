@extends('layouts.app')

@section('title', 'Riwayat Monitoring')

@section('content')
<style>
    /* ===== Banner informasi (gaya sama dengan banner dashboard & compliance score) ===== */
    .sw-alert {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding: 14px 18px;
        border: 1px solid var(--sw-border);
        border-left-width: 5px;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .sw-alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .sw-alert-body {
        flex: 1 1 260px;
        min-width: 0;
    }

    .sw-alert-title {
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.3;
    }

    .sw-alert-text {
        font-size: 0.88rem;
        color: var(--sw-text);
        margin-top: 2px;
    }

    .sw-alert-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 500;
        background-color: #fff;
        white-space: nowrap;
    }

    /* Sukses (hijau lembut) */
    .sw-alert-success {
        background-color: #F0FDF4;
        border-color: #BBF7D0;
        border-left-color: var(--sw-dot-success);
    }

    .sw-alert-success .sw-alert-icon {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .sw-alert-success .sw-alert-title {
        color: #166534;
    }

    .sw-alert-success .sw-alert-action {
        border: 1px solid #86EFAC;
        color: #15803D;
    }

    .sw-alert-success .sw-alert-action:hover {
        background-color: #16A34A;
        border-color: #16A34A;
        color: #fff;
    }
</style>

<div class="topbar">
    <h5>Riwayat Monitoring</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('monitoring.export', request()->query()) }}" class="btn btn-success btn-sm">
            Export Laporan
        </a>
        @if (auth()->user()->role === 'admin')
        <form action="{{ route('monitoring.run') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Cek Sekarang</button>
        </form>
        @endif
    </div>
</div>

@if (!empty($batchId))
<div class="sw-alert sw-alert-success mb-3" role="status">
    <div class="sw-alert-icon"><i class="bi bi-check-circle-fill"></i></div>
    <div class="sw-alert-body">
        <div class="sw-alert-title">Pengecekan selesai</div>
        <div class="sw-alert-text">
            Menampilkan <strong>{{ $logs->count() }} hasil</strong> dari pengecekan yang baru saja dilakukan.
        </div>
    </div>
    <a href="{{ route('monitoring.index') }}" class="btn sw-alert-action">
        Tampilkan semua riwayat
    </a>
</div>
@endif

<form action="{{ route('monitoring.index') }}" method="GET" class="d-flex align-items-end gap-2 mb-3 flex-wrap bg-white p-3 border rounded">
    <div>
        <label class="form-label mb-1">Tampilkan</label>
        <select name="filter_type" id="filter_type" class="form-select form-select-sm" onchange="toggleFilterInputs()">
            <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>Semua Data</option>
            <option value="day" {{ $filterType === 'day' ? 'selected' : '' }}>Per Hari</option>
            <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>Per Bulan</option>
        </select>
    </div>
    <div id="date_input" style="{{ $filterType !== 'day' ? 'display:none;' : '' }}">
        <label class="form-label mb-1">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
    </div>
    <div id="month_input" style="{{ $filterType !== 'month' ? 'display:none;' : '' }}">
        <label class="form-label mb-1">Bulan</label>
        <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
    </div>
    <div class="d-flex align-items-center gap-2 mb-1">
        <input class="judol-toggle" type="checkbox" name="suspicious" value="1" id="suspicious_check" {{ $suspiciousOnly ? 'checked' : '' }}>
        <label class="form-check-label mb-0" for="suspicious_check" style="cursor:pointer;">
            Hanya yang terindikasi judol
        </label>
    </div>
    <div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Terapkan</button>
        @if ($filterType !== 'all' || $suspiciousOnly)
        <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </div>
</form>

<script>
    function toggleFilterInputs() {
        const type = document.getElementById('filter_type').value;
        document.getElementById('date_input').style.display = type === 'day' ? '' : 'none';
        document.getElementById('month_input').style.display = type === 'month' ? '' : 'none';
    }
</script>

<div class="table-responsive bg-white rounded border">
    <table class="table table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th>Waktu Cek</th>
                <th>OPD</th>
                <th>URL</th>
                <th>Status</th>
                <th>Kode HTTP</th>
                <th>Keterangan</th>
                <th>Waktu Respons</th>
                <th>SSL</th>
                <th>SSL Kedaluwarsa</th>
                <th>Konten</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
            <tr>
                <td>{{ $log->checked_at->format('d M Y H:i') }}</td>
                <td>{{ $log->website->opd->nama_opd }}</td>
                <td>{{ $log->website->url }}</td>
                <td>
                    @if ($log->is_up)
                    <span class="badge bg-success">Hidup</span>
                    @else
                    <span class="badge bg-danger">Down</span>
                    @endif
                </td>
                <td>{{ $log->status_code ?? '-' }}</td>
                <td>
                    @if ($log->is_up)
                    <span class="text-muted">-</span>
                    @else
                    <span class="badge bg-warning text-dark">{{ $log->errorTypeLabel() ?? 'Tidak diketahui' }}</span>
                    @endif
                </td>
                <td>{{ $log->response_time_ms ? $log->response_time_ms . ' ms' : '-' }}</td>
                <td>
                    @if (is_null($log->ssl_valid))
                    <span class="badge bg-secondary">N/A</span>
                    @elseif ($log->ssl_valid)
                    <span class="badge bg-success">Valid</span>
                    @else
                    <span class="badge bg-danger">Invalid</span>
                    @endif
                </td>
                <td>{{ $log->ssl_expires_at ? $log->ssl_expires_at->format('d M Y') : '-' }}</td>
                <td>
                    @if ($log->has_suspicious_content)
                    <span class="badge bg-danger" title="{{ $log->suspicious_keywords }}">Terindikasi Judol</span>
                    @else
                    <span class="badge bg-success">Bersih</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Belum ada riwayat monitoring. Klik "Cek Sekarang".</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (empty($batchId))
{{ $logs->links() }}
@endif
@endsection