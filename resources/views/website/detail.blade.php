@extends('layouts.app')

@section('title', 'Detail Website')

@section('content')
<div class="topbar">
    <h5>Detail Riwayat Website</h5>
    <a href="{{ route('website.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

{{-- Info dasar --}}
<div class="bg-white p-3 rounded shadow-sm mb-3">
    <div class="row">
        <div class="col-md-4">
            <div class="text-muted small">Nama OPD</div>
            <div class="fw-semibold">{{ $website->opd->nama_opd }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small">Kategori</div>
            <div class="fw-semibold">{{ $website->opd->kategori }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small">URL Website</div>
            <div class="fw-semibold"><a href="{{ $website->url }}" target="_blank">{{ $website->url }}</a></div>
        </div>
    </div>
</div>

{{-- Filter periode --}}
<form action="{{ route('website.detail', $website->id) }}" method="GET" class="d-flex align-items-end gap-2 mb-3 flex-wrap bg-white p-3 border rounded">
    <div>
        <label class="form-label mb-1">Periode</label>
        <select name="period" id="period" class="form-select form-select-sm" onchange="toggleCustomInputs()">
            <option value="7" {{ $period === '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
            <option value="30" {{ $period === '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
    </div>
    <div id="custom_inputs" class="d-flex align-items-end gap-2" style="{{ $period !== 'custom' ? 'display:none;' : '' }}">
        <div>
            <label class="form-label mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        </div>
        <div>
            <label class="form-label mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        </div>
    </div>
    <div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Terapkan</button>
    </div>
</form>

<script>
    function toggleCustomInputs() {
        const type = document.getElementById('period').value;
        document.getElementById('custom_inputs').style.display = type === 'custom' ? '' : 'none';
    }
</script>

<p class="text-muted small mb-3">
    Statistik di bawah dihitung dari periode
    <strong>{{ $rangeStart->format('d M Y') }}</strong> sampai <strong>{{ $rangeEnd->format('d M Y') }}</strong>.
</p>

{{-- Ringkasan statistik --}}
<div class="row mb-4">
    <div class="col-md-3 col-6">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Persentase Uptime</div>
            <div class="fs-4 fw-bold {{ $uptimePercentage === null ? '' : ($uptimePercentage >= 95 ? 'text-success' : ($uptimePercentage >= 80 ? 'text-warning' : 'text-danger')) }}">
                {{ $uptimePercentage !== null ? $uptimePercentage . '%' : '-' }}
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Kejadian Down</div>
            <div class="fs-4 fw-bold text-danger">{{ $downChecks }}</div>
            <div class="text-muted" style="font-size: 0.75rem;">dari {{ $totalChecks }} kali cek</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Rata-rata Waktu Respons</div>
            <div class="fs-4 fw-bold">{{ $avgResponseTime ? round($avgResponseTime) . ' ms' : '-' }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-white p-3 rounded shadow-sm text-center">
            <div class="text-muted small">Kondisi SSL Saat Ini</div>
            <div class="fs-5 fw-bold">
                @if (!$latestLog || is_null($latestLog->ssl_valid))
                <span class="badge bg-secondary">N/A</span>
                @elseif ($latestLog->ssl_valid)
                <span class="badge bg-success">Valid</span>
                @else
                <span class="badge bg-danger">Invalid</span>
                @endif
            </div>
            @if ($latestLog && $latestLog->ssl_expires_at)
            <div class="text-muted" style="font-size: 0.75rem;">s/d {{ $latestLog->ssl_expires_at->format('d M Y') }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Tabel riwayat lengkap --}}
<div class="bg-white p-3 rounded shadow-sm">
    <h6 class="mb-3">Riwayat Pengecekan pada Periode Ini</h6>
    <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Waktu Cek</th>
                    <th>Status</th>
                    <th>Kode HTTP</th>
                    <th>Keterangan</th>
                    <th>Waktu Respons</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->checked_at->format('d M Y H:i') }}</td>
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
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Tidak ada riwayat pengecekan pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection