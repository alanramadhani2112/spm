@extends('layouts.metronic.app')

@section('title', 'Akun Pengguna')
@section('pageTitle', 'Akun Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

<x-metronic.data-table title="Daftar Akun Pengguna" :headers="['Nama', 'Email', 'Role Saat Ini', 'Status', 'Ubah Role/Status']">
    @forelse($users as $user)
        <tr>
            <td class="fw-bold text-gray-900">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-light-primary">{{ $user->role?->name ?? '—' }}</span></td>
            <td><span class="badge badge-light-{{ ($user->status ?? 'active') === 'active' ? 'success' : 'secondary' }}">{{ $user->status ?? 'active' }}</span></td>
            <td>
                <form method="POST" action="{{ route('superadmin.master-data.users.update', $user) }}" class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf @method('PUT')
                    <select name="role_id" class="form-select form-select-sm form-select-solid w-175px">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select form-select-sm form-select-solid w-125px">
                        <option value="active" @selected(($user->status ?? 'active') === 'active')>active</option>
                        <option value="inactive" @selected(($user->status ?? 'active') === 'inactive')>inactive</option>
                    </select>
                    <button class="btn btn-sm btn-light-primary">Simpan</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-8">Belum ada akun pengguna.</td></tr>
    @endforelse
</x-metronic.data-table>
@endsection
