@extends('layouts.app')

@section('title', 'Tambah Website')

@section('content')
<h5 class="mb-3">Tambah Data Website</h5>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('website.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
    @csrf

    <div class="mb-3">
        <label class="form-label">OPD</label>
        <select name="opd_id" class="form-select" required>
            <option value="">-- Pilih OPD --</option>
            @foreach ($opds as $opd)
            <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>
                {{ $opd->nama_opd }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">URL Website</label>
        <input type="text" name="url" class="form-control" placeholder="https://contoh.go.id" value="{{ old('url') }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('website.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection