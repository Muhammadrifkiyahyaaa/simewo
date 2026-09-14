@extends('layouts.app')

@section('title', 'Data OPD')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data OPD</h5>
    @if (auth()->user()->role === 'admin')
    <a href="{{ route('opd.create') }}" class="btn btn-primary btn-sm">Tambah OPD</a>
    @endif
</div>

<form method="GET" action="{{ route('opd.index') }}" class="bg-white p-3 rounded shadow-sm mb-3 row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label small mb-1">Kategori</label>
        <select name="kategori" class="form-select form-select-sm">
            <option value="">Semua Kategori</option>
            <option value="Dinas" {{ request('kategori') == 'Dinas' ? 'selected' : '' }}>Dinas</option>
            <option value="Badan" {{ request('kategori') == 'Badan' ? 'selected' : '' }}>Badan</option>
            <option value="Kecamatan" {{ request('kategori') == 'Kecamatan' ? 'selected' : '' }}>Kecamatan</option>
            <option value="Sekretariat" {{ request('kategori') == 'Sekretariat' ? 'selected' : '' }}>Sekretariat</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Status Website</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <option value="hidup" {{ request('status') == 'hidup' ? 'selected' : '' }}>Hidup</option>
            <option value="down" {{ request('status') == 'down' ? 'selected' : '' }}>Down</option>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-secondary btn-sm">Terapkan Filter</button>
        <a href="{{ route('opd.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama OPD</th>
                <th>Kategori</th>
                <th>PIC</th>
                <th>Kontak PIC</th>
                <th>Status Website</th>
                @if (auth()->user()->role === 'admin')
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($opds as $opd)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $opd->nama_opd }}</td>
                <td>{{ $opd->kategori }}</td>
                <td>{{ $opd->pic_name }}</td>
                <td>{{ $opd->pic_kontak }}</td>
                <td>
                    @php
                    $anyUp = $opd->websites->contains(function ($website) {
                    $lastLog = $website->monitoringLogs->first();
                    return $lastLog && $lastLog->is_up;
                    });
                    $hasData = $opd->websites->contains(function ($website) {
                    return $website->monitoringLogs->isNotEmpty();
                    });
                    @endphp
                    @if (!$hasData)
                    <span class="badge bg-secondary">Belum dicek</span>
                    @elseif ($anyUp)
                    <span class="badge bg-success">Hidup</span>
                    @else
                    <span class="badge bg-danger">Down</span>
                    @endif
                </td>
                @if (auth()->user()->role === 'admin')
                <td>
                    <a href="{{ route('opd.edit', $opd->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('opd.destroy', $opd->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center">Tidak ada data OPD yang sesuai filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $opds->links() }}
@endsection