@extends('layouts.app')

@section('title', 'Edit Website')

@section('content')
<h5 class="mb-3">Edit Data Website</h5>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('website.update', $website->id) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">OPD</label>
        <select name="opd_id" class="form-select" required>
            @foreach ($opds as $opd)
            <option value="{{ $opd->id }}" {{ $website->opd_id == $opd->id ? 'selected' : '' }}>
                {{ $opd->nama_opd }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">URL Website</label>
        <input type="text" name="url" class="form-control" value="{{ old('url', $website->url) }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('website.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection