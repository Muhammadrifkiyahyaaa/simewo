@extends('layouts.app')

@section('title', 'Peringatan SSL')

@section('content')
<div class="topbar">
    <h5>Peringatan SSL Akan Kedaluwarsa</h5>
    <a href="{{ route('ssl-warnings.export') }}" class="btn btn-success btn-sm">
        Export Excel
    </a>
</div>

<div class="bg-white p-3 rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama OPD</th>
                    <th>URL Website</th>
                    <th>Tanggal Kedaluwarsa</th>
                    <th>Sisa Hari</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                @php
                $sisaHari = (int) floor(now()->startOfDay()->diffInDays($log->ssl_expires_at->copy()->startOfDay(), false));
                $isUrgent = $sisaHari <= $urgentDays;
                    @endphp
                    <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->website->opd->nama_opd }}</td>
                    <td><a href="{{ $log->website->url }}" target="_blank">{{ $log->website->url }}</a></td>
                    <td>{{ $log->ssl_expires_at->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $isUrgent ? 'bg-danger' : 'bg-warning text-dark' }}">
                            {{ $sisaHari }} hari lagi
                        </span>
                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Tidak ada SSL yang akan kedaluwarsa dalam {{ $thresholdDays }} hari ke depan.
                        </td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection