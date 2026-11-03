@extends('layouts.app')

@section('title', 'Laporan Konten Mencurigakan')

@section('content')
<div class="topbar">
    <h5>Laporan Konten Mencurigakan / Judi Online</h5>
    <a href="{{ route('suspicious-content-report.export', request()->query()) }}" class="btn btn-success btn-sm">
        Export Laporan
    </a>
</div>

<form action="{{ route('suspicious-content-report.index') }}" method="GET" class="d-flex align-items-end gap-2 mb-3 flex-wrap bg-white p-3 border rounded">
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
    <div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Terapkan</button>
        @if ($filterType !== 'all')
        <a href="{{ route('suspicious-content-report.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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

<div class="bg-white p-3 rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Waktu Terdeteksi</th>
                    <th>Nama OPD</th>
                    <th>URL Website</th>
                    <th>URL Situs Judi</th>
                    <th>Kata Kunci Terdeteksi</th>
                    <th>PIC OPD</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->checked_at->format('d M Y H:i') }}</td>
                    <td>{{ $log->website->opd->nama_opd }}</td>
                    <td><a href="{{ $log->website->url }}" target="_blank">{{ $log->website->url }}</a></td>
                    <td>
                        @if ($log->suspicious_urls)
                        @foreach (explode("\n", $log->suspicious_urls) as $badUrl)
                        {{-- Sengaja teks biasa (bukan link) agar tidak sengaja terbuka --}}
                        <div class="text-break small text-danger">{{ $badUrl }}</div>
                        @endforeach
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td><span class="badge bg-danger">{{ $log->suspicious_keywords ?? '-' }}</span></td>
                    <td>{{ $log->website->opd->pic_name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Tidak ada konten mencurigakan yang terdeteksi.</td>
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