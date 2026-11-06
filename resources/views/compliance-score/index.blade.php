@extends('layouts.app')

@section('title', 'Compliance Score')

@section('content')
<style>
    /* ===== Banner informasi (gaya sama dengan banner dashboard) ===== */
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

    .sw-alert-warning {
        background-color: #FFFBEB;
        border-color: #FDE68A;
        border-left-color: var(--sw-dot-warning);
    }

    .sw-alert-warning .sw-alert-icon {
        background-color: #FEF3C7;
        color: #B45309;
    }

    .sw-alert-warning .sw-alert-title {
        color: #92400E;
    }
</style>

<div class="topbar">
    <h5>Compliance Score</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('compliance-score.export', request()->query()) }}" class="btn btn-success btn-sm">
            Export Laporan
        </a>
        @if (auth()->user()->role === 'admin')
        <form action="{{ route('compliance-score.recalculate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Hitung Ulang Sekarang</button>
        </form>
        @endif
    </div>
</div>

<div class="sw-alert sw-alert-warning mb-3" role="note">
    <div class="sw-alert-icon"><i class="bi bi-info-circle-fill"></i></div>
    <div class="sw-alert-body">
        <div class="sw-alert-title">Cara perhitungan Skor Uptime</div>
        <div class="sw-alert-text">
            Skor Uptime dihitung dari data monitoring <strong>30 hari terakhir</strong>. Website yang belum punya data monitoring
            dalam 30 hari terakhir akan tampil "Belum ada data" dan tidak diikutkan dalam perhitungan.
        </div>
    </div>
</div>

<form method="GET" action="{{ route('compliance-score.index') }}" class="bg-white p-3 rounded shadow-sm mb-3 row g-2 align-items-end">
    <div class="col-md-4">
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
        <label class="form-label small mb-1">Urutkan</label>
        <select name="sort" class="form-select form-select-sm">
            <option value="terendah" {{ $sort === 'terendah' ? 'selected' : '' }}>Skor Terendah Dulu (perlu perhatian)</option>
            <option value="tertinggi" {{ $sort === 'tertinggi' ? 'selected' : '' }}>Skor Tertinggi Dulu</option>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
        <a href="{{ route('compliance-score.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

<div class="bg-white p-3 rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama OPD</th>
                    <th>URL Website</th>
                    <th class="text-center">Skor Domain<br><span class="fw-normal">(maks 20)</span></th>
                    <th class="text-center">Skor SSL<br><span class="fw-normal">(maks 30)</span></th>
                    <th class="text-center">Skor Uptime<br><span class="fw-normal">(maks 50)</span></th>
                    <th class="text-center">Total Skor</th>
                    <th class="text-center">Kategori</th>
                    <th>Terakhir Dihitung</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['opd']->nama_opd }}</td>
                    <td><a href="{{ $row['website']->url }}" target="_blank">{{ $row['website']->url }}</a></td>

                    @if ($row['score'])
                    @php $kategori = \App\Services\ComplianceScoreService::kategori($row['score']->total_skor); @endphp
                    <td class="text-center">{{ $row['score']->skor_domain_resmi }}</td>
                    <td class="text-center">{{ $row['score']->skor_ssl_aktif }}</td>
                    <td class="text-center">{{ $row['score']->skor_uptime }}</td>
                    <td class="text-center fw-semibold">{{ $row['score']->total_skor }}</td>
                    <td class="text-center">
                        <span class="badge {{ $kategori['badge'] }}">{{ $kategori['label'] }}</span>
                    </td>
                    <td>{{ $row['score']->calculated_at->format('d M Y H:i') }}</td>
                    @else
                    <td class="text-center text-muted" colspan="5">Belum ada data</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data website yang sesuai filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection