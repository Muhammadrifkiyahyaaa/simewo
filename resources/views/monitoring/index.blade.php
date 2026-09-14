@extends('layouts.app')

@section('title', 'Riwayat Monitoring')

@section('content')
<div class="topbar">
    <h5>Riwayat Monitoring</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('monitoring.export', request()->query()) }}" class="btn btn-success btn-sm">
            Export Excel
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
<div class="alert alert-success d-flex align-items-center justify-content-between mb-3">
    <div>
        <i class="bi bi-check2-circle me-2"></i>
        Menampilkan <strong>{{ $logs->count() }} hasil</strong> dari pengecekan yang baru saja dilakukan.
    </div>
    <a href="{{ route('monitoring.index') }}" class="text-decoration-underline text-reset">Tampilkan semua riwayat &raquo;</a>
</div>
@endif

@if ($suspiciousOnly)
<div class="alert alert-danger d-flex align-items-center justify-content-between mb-3">
    <div>
        <i class="bi bi-exclamation-octagon me-2"></i>
        Sedang menampilkan hanya riwayat yang <strong>terindikasi konten judol</strong>.
    </div>
    <a href="{{ route('monitoring.index') }}" class="text-decoration-underline text-reset">Tampilkan semua &raquo;</a>
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