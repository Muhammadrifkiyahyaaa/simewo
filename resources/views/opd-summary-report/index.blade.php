@extends('layouts.app')

@section('title', 'Laporan Rekap per OPD')

@section('content')
<div class="topbar">
    <h5>Laporan Rekap per OPD</h5>
    <a href="{{ route('opd-summary-report.export', request()->query()) }}" class="btn btn-success btn-sm">
        Export Laporan
    </a>
</div>

<form method="GET" action="{{ route('opd-summary-report.index') }}" class="bg-white p-3 rounded shadow-sm mb-3 row g-2 align-items-end">
    <div class="col-md-8">
        <label class="form-label small mb-1">Kategori OPD</label>
        <select name="kategori" class="form-select form-select-sm">
            <option value="">Semua Kategori</option>
            <option value="Dinas" {{ request('kategori') == 'Dinas' ? 'selected' : '' }}>Dinas</option>
            <option value="Badan" {{ request('kategori') == 'Badan' ? 'selected' : '' }}>Badan</option>
            <option value="Kecamatan" {{ request('kategori') == 'Kecamatan' ? 'selected' : '' }}>Kecamatan</option>
            <option value="Sekretariat" {{ request('kategori') == 'Sekretariat' ? 'selected' : '' }}>Sekretariat</option>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
        <a href="{{ route('opd-summary-report.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<div class="bg-white p-3 rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama OPD</th>
                    <th>Kategori</th>
                    <th>PIC</th>
                    <th class="text-center">Jumlah Website</th>
                    <th class="text-center">Hidup</th>
                    <th class="text-center">Down</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opds as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['opd']->nama_opd }}</td>
                    <td>{{ $row['opd']->kategori }}</td>
                    <td>{{ $row['opd']->pic_name }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center"><span class="badge bg-success">{{ $row['hidup'] }}</span></td>
                    <td class="text-center"><span class="badge bg-danger">{{ $row['down'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Tidak ada data OPD.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection