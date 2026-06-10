@extends('layouts.metronic.app')

@section('title', 'Role & Permission')
@section('pageTitle', 'Role & Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

<div class="d-grid gap-6">
    @foreach($roles as $role)
        <x-metronic.card title="{{ $role->name }}">
            <x-slot:header>
                <span class="badge badge-light-primary">{{ $role->parameter }}</span>
            </x-slot:header>

            <form method="POST"
                  action="{{ route('superadmin.master-data.roles.permissions.update', $role) }}"
                  data-swal-confirm="true"
                  data-swal-title="Simpan permission role?"
                  data-swal-text="Permission untuk role {{ $role->name }} akan diperbarui. Pastikan akses sudah sesuai."
                  data-swal-icon="warning"
                  data-swal-confirm-button="Ya, simpan"
                  data-swal-confirm-class="btn btn-primary">
                @csrf @method('PUT')

                @if($role->parameter === 'super_admin')
                    <x-metronic.alert type="info" message="Super Admin otomatis memiliki seluruh permission. Jika disimpan, semua permission akan tetap disinkronkan." />
                @endif

                <div class="row g-5">
                    @foreach($permissions as $group => $groupPermissions)
                        <div class="col-xl-4 col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="fw-bold text-gray-900 text-uppercase fs-8 mb-4">{{ $group }}</div>
                                <div class="d-grid gap-3">
                                    @foreach($groupPermissions as $permission)
                                        <label class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id) || $role->parameter === 'super_admin') @disabled($role->parameter === 'super_admin')>
                                            <span class="form-check-label">
                                                <span class="d-block fw-semibold text-gray-800">{{ $permission->name }}</span>
                                                <span class="d-block fs-8 text-muted">{{ $permission->key }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 d-flex justify-content-end">
                    <button class="btn btn-primary">Simpan Permission {{ $role->name }}</button>
                </div>
            </form>
        </x-metronic.card>
    @endforeach
</div>
@endsection
