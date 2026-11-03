@extends('layouts.app')

@section('title', 'Data Website')

@section('content')
<div class="topbar">
    <h5>Data Website</h5>
    @if (auth()->user()->role === 'admin')
    <a href="{{ route('website.create') }}" class="btn btn-primary btn-sm">Tambah Website</a>
    @endif
</div>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama OPD</th>
                <th>URL</th>
                <th>Detail</th>
                @if (auth()->user()->role === 'admin')
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($websites as $website)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $website->opd->nama_opd }}</td>
                <td><a href="{{ $website->url }}" target="_blank">{{ $website->url }}</a></td>
                <td>
                    <a href="{{ route('website.detail', $website->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                </td>
                @if (auth()->user()->role === 'admin')
                <td>
                    <a href="{{ route('website.edit', $website->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('website.destroy', $website->id) }}" method="POST" class="d-inline form-delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->role === 'admin' ? 5 : 4 }}" class="text-center">Belum ada data website.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $websites->links() }}
@endsection