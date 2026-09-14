@extends('layouts.app')

@section('title', 'Tambah OPD')

@section('content')
<h3>Tambah Data OPD</h3>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('opd.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
    @csrf

    <div class="mb-3">
        <label class="form-label">Nama OPD</label>
        <input type="text" name="nama_opd" class="form-control" value="{{ old('nama_opd') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="Dinas" {{ old('kategori') == 'Dinas' ? 'selected' : '' }}>Dinas</option>
            <option value="Badan" {{ old('kategori') == 'Badan' ? 'selected' : '' }}>Badan</option>
            <option value="Kecamatan" {{ old('kategori') == 'Kecamatan' ? 'selected' : '' }}>Kecamatan</option>
            <option value="Sekretariat" {{ old('kategori') == 'Sekretariat' ? 'selected' : '' }}>Sekretariat</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Nama PIC</label>
        <input type="text" name="pic_name" class="form-control" value="{{ old('pic_name') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kontak PIC</label>
        <input type="text" name="pic_kontak" class="form-control" value="{{ old('pic_kontak') }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('opd.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection