@extends('layouts.app')

@section('title', 'Kata Diblokir')

@section('content')
<div class="topbar">
    <h5>Kata Diblokir (Deteksi Judol)</h5>
</div>

@if (auth()->user()->role === 'admin')
<div class="bg-white p-3 rounded border mb-3">
    <form action="{{ route('suspicious-keywords.store') }}" method="POST" class="d-flex gap-2 flex-wrap">
        @csrf
        <input
            type="text"
            name="keyword"
            class="form-control form-control-sm @error('keyword') is-invalid @enderror"
            style="max-width: 300px;"
            placeholder="Contoh: slot gacor"
            value="{{ old('keyword') }}"
            required>
        <button type="submit" class="btn btn-primary btn-sm">Tambah Kata</button>
    </form>
    @error('keyword')
    <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
@endif

<div class="bg-white p-3 rounded border">
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Kata Kunci</th>
                    @if (auth()->user()->role === 'admin')
                    <th style="width: 100px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($keywords as $i => $kw)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $kw->keyword }}</td>
                    @if (auth()->user()->role === 'admin')
                    <td>
                        <form action="{{ route('suspicious-keywords.destroy', $kw) }}" method="POST"
                            onsubmit="return confirm('Hapus kata &quot;{{ $kw->keyword }}&quot; dari daftar?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'admin' ? 3 : 2 }}" class="text-center">
                        Belum ada kata kunci.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection