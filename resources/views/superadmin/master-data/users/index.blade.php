@extends('layouts.metronic.app')

@section('title', 'Akun Pengguna')
@section('pageTitle', 'Akun Pengguna')

@section('toolbar')
<div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#invite-user-modal">
        <i class="ki-outline ki-plus fs-3"></i>Tambah / Undang Pengguna
    </button>
    <a href="{{ route('superadmin.master-data.roles.index') }}" class="btn btn-sm btn-light-primary">
        <i class="ki-outline ki-security-user fs-3"></i>Role & Permission
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
    $statusColorMap = [
        'active' => 'success',
        'inactive' => 'secondary',
    ];
    $unlinkedSsoCount = max(($userStats['total'] ?? 0) - ($userStats['linked_sso'] ?? 0), 0);
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <span class="symbol symbol-45px flex-shrink-0">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-profile-user fs-2 text-white"></i></span>
                </span>
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Control Center Akses Pengguna</h2>
                    <div class="fs-7 text-muted">Pantau akun, role aktif, dan status akses sebelum pengguna masuk ke workflow akreditasi melalui Muhammadiyah ID.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-light-primary">{{ $users->count() }} ditampilkan</span>
                <span class="badge badge-light-info">{{ $userStats['linked_sso'] ?? 0 }} terhubung SSO</span>
                @if($hasFilters)
                    <span class="badge badge-light-warning">Filter aktif</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $userStats['total'] ?? 0 }}" label="Total Akun" icon="ki-profile-user" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $userStats['active'] ?? 0 }}" label="Akun Aktif" icon="ki-shield-tick" color="success" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $userStats['inactive'] ?? 0 }}" label="Nonaktif" icon="ki-lock" color="secondary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $userStats['linked_sso'] ?? 0 }}" label="Terhubung SSO" icon="ki-verify" color="info" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $unlinkedSsoCount }}" label="Menunggu SSO" icon="ki-time" color="warning" /></div>
</div>

<x-metronic.modal id="invite-user-modal" title="Tambah / Undang Pengguna" size="lg">
    <form id="invite-user-form"
          method="POST"
          action="{{ route('superadmin.master-data.users.store') }}"
          class="row g-4 text-start"
          data-swal-confirm="true"
          data-swal-title="Undang pengguna baru?"
          data-swal-text="Akun lokal akan dibuat tanpa password manual dan siap ditautkan saat pengguna login Muhammadiyah ID."
          data-swal-icon="question"
          data-swal-confirm-button="Ya, undang"
          data-swal-confirm-class="btn btn-primary">
        @csrf

        <div class="col-12">
            <div class="rounded bg-light-info border border-info border-dashed p-4">
                <div class="fw-semibold text-gray-900 mb-1">Pre-registration untuk Muhammadiyah ID</div>
                <div class="fs-7 text-muted">Super Admin menentukan role lokal terlebih dahulu. Identitas SSO akan ditautkan saat pengguna login menggunakan Muhammadiyah ID.</div>
            </div>
        </div>

        <div class="col-md-6">
            <label for="invite_name" class="form-label required">Nama</label>
            <input id="invite_name" type="text" name="name" value="{{ old('name') }}" class="form-control form-control-solid" placeholder="Nama lengkap pengguna" required>
        </div>
        <div class="col-md-6">
            <label for="invite_email" class="form-label required">Email Muhammadiyah ID</label>
            <input id="invite_email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-solid" placeholder="nama@example.com" required>
            <div class="fs-8 text-muted mt-1">Email dipakai sebagai kandidat pencocokan saat callback SSO.</div>
        </div>
        <div class="col-md-6">
            <label for="invite_m_id" class="form-label">Muhammadiyah ID</label>
            <input id="invite_m_id" type="text" name="m_id" value="{{ old('m_id') }}" class="form-control form-control-solid" placeholder="0000 0000 0000 0000">
        </div>
        <div class="col-md-6">
            <label for="invite_nbm" class="form-label">NBM</label>
            <input id="invite_nbm" type="text" name="nbm" value="{{ old('nbm') }}" class="form-control form-control-solid" placeholder="Contoh: 123456">
        </div>
        <div class="col-md-6">
            <label for="invite_role_id" class="form-label required">Role Lokal</label>
            <select id="invite_role_id" name="role_id" class="form-select form-select-solid" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Role lokal tetap menjadi sumber authorization sistem.</div>
        </div>
        <div class="col-md-6">
            <label for="invite_status" class="form-label required">Status</label>
            <select id="invite_status" name="status" class="form-select form-select-solid" required>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" form="invite-user-form" class="btn btn-primary">Undang Pengguna</button>
    </x-slot:footer>
</x-metronic.modal>

<x-metronic.card title="Filter Akun" class="mb-8">
    <x-slot:header>
        @if($hasFilters)
            <span class="badge badge-light-primary">Hasil: {{ $users->count() }} akun</span>
        @else
            <span class="badge badge-light-secondary">Semua akun</span>
        @endif
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.master-data.users.index') }}" class="row g-5 align-items-end">
        <div class="col-xl-4 col-md-6">
            <label for="q" class="form-label">Cari Akun</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-solid" placeholder="Nama, email, UUID, M-ID, NBM, atau role">
        </div>
        <div class="col-xl-3 col-md-6">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" class="form-select form-select-solid">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(($filters['role'] ?? '') == $role->id)>{{ $role->name }} ({{ $role->users_count }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-3 col-md-6">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select form-select-solid">
                <option value="">Semua Status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-2 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="ki-outline ki-filter fs-4"></i>Filter
            </button>
            <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-light" aria-label="Reset filter akun pengguna">Reset</a>
        </div>
    </form>
</x-metronic.card>

<x-metronic.card title="Daftar Akun Pengguna" flush>
    <x-slot:header>
        <span class="badge badge-light-primary">{{ $users->count() }} akun</span>
    </x-slot:header>

    @if($users->isEmpty())
        <div class="text-center py-12 text-muted border rounded bg-light">
            <i class="ki-outline ki-search-list fs-2x text-gray-400 mb-3"></i>
            <div class="fw-semibold text-gray-800 mb-1">Tidak ada akun yang cocok.</div>
            <div class="fs-7 mb-4">Coba ubah filter pencarian atau reset untuk melihat seluruh akun.</div>
            <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-sm btn-light-primary">Reset Filter</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0 bg-light">
                        <th class="ps-4 min-w-260px">Pengguna</th>
                        <th class="min-w-170px">Role</th>
                        <th class="min-w-150px">Muhammadiyah ID</th>
                        <th class="min-w-140px">Status</th>
                        <th class="min-w-170px">Terdaftar</th>
                        <th class="text-end min-w-90px pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($users as $user)
                        @php
                            $roleParameter = $user->role?->parameter ?? 'unknown';
                            $roleColor = $roleColorMap[$roleParameter] ?? 'secondary';
                            $status = $user->status ?? 'active';
                            $statusColor = $statusColorMap[$status] ?? 'secondary';
                            $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U';
                            $isSsoLinked = filled($user->sso_id);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="symbol symbol-45px flex-shrink-0">
                                        <span class="symbol-label bg-light-{{ $roleColor }} text-{{ $roleColor }} fw-bold">{{ $initials }}</span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-gray-900">{{ $user->name }}</div>
                                        <div class="fs-8 text-muted">{{ $user->email }}</div>
                                        <div class="fs-8 text-muted font-monospace">{{ $user->uuid ? \Illuminate\Support\Str::limit($user->uuid, 18, '...') : 'ID: '.$user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $roleColor }}">{{ $user->role?->name ?? 'Belum ada role' }}</span>
                                <div class="fs-8 text-muted font-monospace mt-1">{{ $roleParameter }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $isSsoLinked ? 'info' : 'warning' }}">{{ $isSsoLinked ? 'Terhubung SSO' : 'Menunggu SSO' }}</span>
                                <div class="fs-8 text-muted mt-1">M-ID: {{ $user->m_id ?: '—' }}</div>
                                <div class="fs-8 text-muted">NBM: {{ $user->nbm ?: '—' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $statusColor }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                <div class="fs-8 text-muted mt-1">{{ $status === 'active' ? 'Dapat mengakses sistem' : 'Akses dinonaktifkan' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-gray-900">{{ $user->created_at?->format('d M Y') ?? '—' }}</div>
                                <div class="fs-8 text-muted">SSO: {{ $user->last_sso_login_at?->diffForHumans() ?? 'belum login' }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <x-superadmin.action-menu label="Buka aksi pengguna {{ $user->name }}">
                                    <div class="menu-item px-3">
                                        <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#edit-user-{{ $user->id }}">
                                            <i class="ki-outline ki-profile-user fs-4"></i>
                                            <span>Edit Role/Status</span>
                                        </button>
                                    </div>
                                </x-superadmin.action-menu>

                                <x-metronic.modal id="edit-user-{{ $user->id }}" title="Edit Akun Pengguna" size="md">
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

                                        <div class="rounded bg-light-primary p-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="symbol symbol-40px flex-shrink-0"><span class="symbol-label bg-primary text-white fw-bold">{{ $initials }}</span></span>
                                                <div>
                                                    <div class="fw-bold text-gray-900">{{ $user->name }}</div>
                                                    <div class="fs-8 text-muted">{{ $user->email }}</div>
                                                    <div class="fs-8 text-muted">{{ $isSsoLinked ? 'Sudah terhubung Muhammadiyah ID' : 'Belum terhubung Muhammadiyah ID' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="role_id_{{ $user->id }}" class="form-label required">Role</label>
                                            <select id="role_id_{{ $user->id }}" name="role_id" class="form-select form-select-solid" required>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="fs-8 text-muted mt-1">Perubahan role akan memengaruhi akses menu dan workflow.</div>
                                        </div>
                                        <div>
                                            <label for="status_{{ $user->id }}" class="form-label required">Status</label>
                                            <select id="status_{{ $user->id }}" name="status" class="form-select form-select-solid" required>
                                                @foreach($statusOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <div class="fs-8 text-muted mt-1">Nonaktifkan akun bila akses perlu dihentikan sementara.</div>
                                        </div>
                                        <div>
                                            <label for="user_reason_{{ $user->id }}" class="form-label required">Alasan Perubahan</label>
                                            <textarea id="user_reason_{{ $user->id }}" name="reason" class="form-control form-control-solid" rows="3" placeholder="Jelaskan alasan perubahan role/status akun ini" required></textarea>
                                            <div class="fs-8 text-muted mt-1">Alasan akan tersimpan di audit log.</div>
                                        </div>
                                    </form>
                                    <x-slot:footer>
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" form="edit-user-form-{{ $user->id }}" class="btn btn-primary">Simpan Perubahan</button>
                                    </x-slot:footer>
                                </x-metronic.modal>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-metronic.card>
@endsection
