@extends('layouts.metronic.app')

@section('title', 'Akun Pengguna')
@section('pageTitle', 'Akun Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

<x-metronic.data-table title="Daftar Akun Pengguna" :headers="['Nama', 'Email', 'Role Saat Ini', 'Status', 'Aksi']">
    @forelse($users as $user)
        <tr>
            <td class="fw-bold text-gray-900">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-light-primary">{{ $user->role?->name ?? '—' }}</span></td>
            <td><span class="badge badge-light-{{ ($user->status ?? 'active') === 'active' ? 'success' : 'secondary' }}">{{ $user->status ?? 'active' }}</span></td>
            <td class="text-end">
                <x-superadmin.action-menu label="Buka aksi pengguna {{ $user->name }}">
                    <div class="menu-item px-3">
                        <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#edit-user-{{ $user->id }}">
                            <i class="ki-outline ki-profile-user fs-4"></i>
                            <span>Edit Role/Status</span>
                        </button>
                    </div>
                </x-superadmin.action-menu>

                <x-metronic.modal id="edit-user-{{ $user->id }}" title="Edit Akun Pengguna" size="sm">
                    <form id="edit-user-form-{{ $user->id }}"
                          method="POST"
                          action="{{ route('superadmin.master-data.users.update', $user) }}"
                          class="d-grid gap-4 text-start"
                          data-swal-confirm="true"
                          data-swal-title="Simpan perubahan akun?"
                          data-swal-text="Role atau status akun {{ $user->name }} akan diperbarui."
                          data-swal-icon="warning"
                          data-swal-confirm-button="Ya, simpan"
                          data-swal-confirm-class="btn btn-primary">
                        @csrf @method('PUT')
                        <div>
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select form-select-solid">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="active" @selected(($user->status ?? 'active') === 'active')>active</option>
                                <option value="inactive" @selected(($user->status ?? 'active') === 'inactive')>inactive</option>
                            </select>
                        </div>
                    </form>
                    <x-slot:footer>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" form="edit-user-form-{{ $user->id }}" class="btn btn-primary">Simpan</button>
                    </x-slot:footer>
                </x-metronic.modal>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-8">Belum ada akun pengguna.</td></tr>
    @endforelse
</x-metronic.data-table>
@endsection
