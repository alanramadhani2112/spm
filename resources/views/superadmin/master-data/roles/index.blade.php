@extends('layouts.metronic.app')

@section('title', 'Role & Permission')
@section('pageTitle', 'Role & Permission')

@section('toolbar')
<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-sm btn-light-primary">
        <i class="ki-outline ki-profile-user fs-3"></i>Akun Pengguna
    </a>
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-left fs-4"></i>Kembali
    </a>
</div>
@endsection

@section('content')
@php
    $roleColorMap = [
        'super_admin' => 'danger',
        'admin' => 'primary',
        'asesor' => 'info',
        'pesantren' => 'success',
    ];
    $roleIconMap = [
        'super_admin' => 'ki-shield-tick',
        'admin' => 'ki-setting-2',
        'asesor' => 'ki-award',
        'pesantren' => 'ki-bank',
    ];
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <span class="symbol symbol-45px flex-shrink-0">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-security-user fs-2 text-white"></i></span>
                </span>
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Matriks Role & Permission</h2>
                    <div class="fs-7 text-muted">Kelola cakupan akses setiap role agar workflow akreditasi tetap aman, terukur, dan mudah diaudit.</div>
                </div>
            </div>
            <span class="badge badge-light-primary">{{ $roleStats['total'] ?? $roles->count() }} role aktif</span>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $roleStats['total'] ?? 0 }}" label="Total Role" icon="ki-security-user" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $roleStats['permissions'] ?? 0 }}" label="Permission Tersedia" icon="ki-key" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $roleStats['assigned_permissions'] ?? 0 }}" label="Permission Tertaut" icon="ki-check-circle" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $roleStats['with_users'] ?? 0 }}" label="Role Berpengguna" icon="ki-profile-user" color="warning" /></div>
</div>

<div class="card card-flush border border-warning border-dashed bg-light-warning mb-8">
    <div class="card-body p-5">
        <div class="d-flex align-items-start gap-4">
            <span class="symbol symbol-40px flex-shrink-0">
                <span class="symbol-label bg-warning"><i class="ki-outline ki-information-4 fs-2 text-white"></i></span>
            </span>
            <div>
                <div class="fw-bold text-gray-900 mb-1">Mode aman aktif</div>
                <div class="fs-7 text-muted">Permission ditampilkan read-only. Untuk mengubah akses, buka tombol <span class="fw-semibold text-gray-800">Edit Permission</span> pada role terkait lalu konfirmasi penyimpanan.</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-gray-900 mb-0">Ringkasan Role</h3>
    <span class="fs-8 text-muted">Role Super Admin dikunci dengan akses penuh.</span>
</div>

<div class="row g-5 g-xl-8 mb-8">
    @foreach($roles as $role)
        @php
            $roleColor = $roleColorMap[$role->parameter] ?? 'secondary';
            $roleIcon = $roleIconMap[$role->parameter] ?? 'ki-user';
            $activePermissionCount = $role->parameter === 'super_admin' ? $totalPermissions : $role->permissions->count();
        @endphp
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100 border border-gray-200 hover-elevate-up">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="symbol symbol-45px">
                            <span class="symbol-label bg-light-{{ $roleColor }}"><i class="ki-outline {{ $roleIcon }} fs-2 text-{{ $roleColor }}"></i></span>
                        </span>
                        <span class="badge badge-light-{{ $roleColor }}">{{ $activePermissionCount }} permission</span>
                    </div>
                    <div class="fw-bold text-gray-900 mb-1">{{ $role->name }}</div>
                    <div class="fs-8 text-muted font-monospace mb-4">{{ $role->parameter }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-light-secondary">{{ $role->users_count }} akun</span>
                        @if($role->parameter === 'super_admin')
                            <span class="badge badge-light-danger">Full access</span>
                        @else
                            <button type="button" class="btn btn-sm btn-light-{{ $roleColor }}" data-bs-toggle="modal" data-bs-target="#edit-role-permissions-{{ $role->id }}">
                                Edit Permission
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-grid gap-8">
    @forelse($roles as $role)
        @php
            $roleColor = $roleColorMap[$role->parameter] ?? 'secondary';
            $roleIcon = $roleIconMap[$role->parameter] ?? 'ki-user';
            $isSuperAdmin = $role->parameter === 'super_admin';
            $activePermissionCount = $isSuperAdmin ? $totalPermissions : $role->permissions->count();
        @endphp
        <x-metronic.card class="border border-gray-200" bodyClass="pt-0">
            <x-slot:header>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 w-100">
                    <div class="d-flex align-items-center gap-4">
                        <span class="symbol symbol-45px flex-shrink-0">
                            <span class="symbol-label bg-light-{{ $roleColor }}"><i class="ki-outline {{ $roleIcon }} fs-2 text-{{ $roleColor }}"></i></span>
                        </span>
                        <div>
                            <h3 class="fw-bold text-gray-900 mb-1">{{ $role->name }}</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-light-{{ $roleColor }} font-monospace">{{ $role->parameter }}</span>
                                <span class="badge badge-light-secondary">{{ $role->users_count }} akun</span>
                                <span class="badge badge-light-primary">{{ $activePermissionCount }} / {{ $totalPermissions }} permission</span>
                            </div>
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <span class="badge badge-light-danger">Terkunci</span>
                    @else
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#edit-role-permissions-{{ $role->id }}">
                            <i class="ki-outline ki-pencil fs-4"></i>Edit Permission
                        </button>
                    @endif
                </div>
            </x-slot:header>

            @if($isSuperAdmin)
                <x-metronic.alert type="info">
                    <div class="fw-semibold mb-1">Super Admin dikunci dengan akses penuh.</div>
                    <div>Semua permission ditampilkan sebagai akses aktif dan tidak dapat dimatikan dari UI ini.</div>
                </x-metronic.alert>
            @endif

            @if($permissions->isEmpty())
                <div class="text-center py-12 text-muted border rounded bg-light">
                    <i class="ki-outline ki-key fs-2x text-gray-400 mb-3"></i>
                    <div class="fw-semibold text-gray-800 mb-1">Belum ada permission.</div>
                    <div class="fs-7">Tambahkan permission melalui seeder atau migrasi sebelum mengatur role.</div>
                </div>
            @else
                <div class="row g-5">
                    @foreach($permissions as $group => $groupPermissions)
                        @php
                            $activeGroupPermissions = $isSuperAdmin
                                ? $groupPermissions
                                : $groupPermissions->filter(fn ($permission) => $role->permissions->contains('id', $permission->id));
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="border rounded p-5 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                                    <div class="fw-bold text-gray-900 text-uppercase fs-8">{{ $group }}</div>
                                    <span class="badge badge-light-{{ $activeGroupPermissions->isNotEmpty() ? $roleColor : 'secondary' }}">{{ $activeGroupPermissions->count() }} / {{ $groupPermissions->count() }}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($activeGroupPermissions as $permission)
                                        <span class="badge badge-light-{{ $roleColor }} text-start" title="{{ $permission->key }}">{{ $permission->name }}</span>
                                    @empty
                                        <span class="fs-8 text-muted">Tidak ada permission aktif pada grup ini.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-metronic.card>

        @unless($isSuperAdmin)
            <x-metronic.modal id="edit-role-permissions-{{ $role->id }}" title="Edit Permission {{ $role->name }}" size="xl" scrollable>
                <form id="edit-role-permissions-form-{{ $role->id }}"
                      method="POST"
                      action="{{ route('superadmin.master-data.roles.permissions.update', $role) }}"
                      data-swal-confirm="true"
                      data-swal-title="Simpan perubahan permission {{ $role->name }}?"
                      data-swal-text="Periksa ulang permission yang dicentang. Akses yang dicabut langsung berdampak pada {{ $role->users_count }} akun dalam role ini."
                      data-swal-icon="warning"
                      data-swal-confirm-button="Ya, simpan"
                      data-swal-confirm-class="btn btn-primary">
                    @csrf @method('PUT')

                    <div class="rounded bg-light-warning border border-warning border-dashed p-4 mb-6">
                        <div class="fw-semibold text-gray-900 mb-1">Review sebelum simpan</div>
                        <div class="fs-7 text-muted">Checkbox hanya aktif di modal ini untuk mengurangi perubahan tidak sengaja. Pastikan role, jumlah akun, dan cakupan permission sudah benar.</div>
                    </div>

                    @if($permissions->isEmpty())
                        <div class="text-center py-12 text-muted border rounded bg-light">Belum ada permission.</div>
                    @else
                        <div class="row g-5">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="col-xl-4 col-md-6">
                                    <div class="border rounded p-5 h-100 bg-white">
                                        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                                            <div class="fw-bold text-gray-900 text-uppercase fs-8">{{ $group }}</div>
                                            <span class="badge badge-light-secondary">{{ $groupPermissions->count() }} item</span>
                                        </div>
                                        <div class="d-grid gap-3">
                                            @foreach($groupPermissions as $permission)
                                                @php $isChecked = $role->permissions->contains('id', $permission->id); @endphp
                                                <label class="form-check form-check-custom form-check-solid align-items-start">
                                                    <input class="form-check-input mt-1" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($isChecked)>
                                                    <span class="form-check-label">
                                                        <span class="d-block fw-semibold text-gray-800">{{ $permission->name }}</span>
                                                        <span class="d-block fs-8 text-muted font-monospace">{{ $permission->key }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </form>
                <x-slot:footer>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="edit-role-permissions-form-{{ $role->id }}" class="btn btn-primary" @disabled($permissions->isEmpty())>
                        Simpan Permission {{ $role->name }}
                    </button>
                </x-slot:footer>
            </x-metronic.modal>
        @endunless
    @empty
        <div class="text-center py-12 text-muted border rounded bg-light">
            <i class="ki-outline ki-security-user fs-2x text-gray-400 mb-3"></i>
            <div class="fw-semibold text-gray-800 mb-1">Belum ada role.</div>
            <div class="fs-7">Role akan muncul setelah data awal sistem tersedia.</div>
        </div>
    @endforelse
</div>
@endsection
