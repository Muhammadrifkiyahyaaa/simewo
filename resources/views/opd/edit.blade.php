@extends('layouts.app')

@section('title', 'Edit OPD')

@section('content')
<h5 class="mb-3">Edit Data OPD</h5>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('opd.update', $opd->id) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Nama OPD</label>
        <input type="text" name="nama_opd" class="form-control" value="{{ old('nama_opd', $opd->nama_opd) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="Dinas" {{ old('kategori', $opd->kategori) == 'Dinas' ? 'selected' : '' }}>Dinas</option>
            <option value="Badan" {{ old('kategori', $opd->kategori) == 'Badan' ? 'selected' : '' }}>Badan</option>
            <option value="Kecamatan" {{ old('kategori', $opd->kategori) == 'Kecamatan' ? 'selected' : '' }}>Kecamatan</option>
            <option value="Sekretariat" {{ old('kategori', $opd->kategori) == 'Sekretariat' ? 'selected' : '' }}>Sekretariat</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Nama PIC</label>
        <input type="text" name="pic_name" class="form-control" value="{{ old('pic_name', $opd->pic_name) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kontak PIC</label>
        <input type="text" name="pic_kontak" class="form-control" value="{{ old('pic_kontak', $opd->pic_kontak) }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('opd.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection