@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Manajemen Pengguna</h5>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Tambah Pengguna</a>
</div>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if ($user->role === 'admin')
                    <span class="badge bg-primary">Admin</span>
                    @else
                    <span class="badge bg-secondary">Staf</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    @if ($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin hapus akun ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada data pengguna.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $users->links() }}
@endsection