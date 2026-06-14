@extends('layouts.metronic.app')

@section('title', 'Detail Akun Pengguna')
@section('pageTitle', 'Detail Akun Pengguna')

@section('toolbar')
<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-sm btn-light">
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
    $roleParameter = $user->role?->parameter ?? 'unknown';
    $roleColor = $roleColorMap[$roleParameter] ?? 'secondary';
    $status = $user->status ?? 'active';
    $statusColor = $statusColorMap[$status] ?? 'secondary';
    $isSsoLinked = filled($user->sso_id);
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U';
    $ssoGroups = collect($user->sso_groups ?? [])->filter()->values();
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-center gap-4">
                <span class="symbol symbol-60px flex-shrink-0">
                    <span class="symbol-label bg-light-{{ $roleColor }} text-{{ $roleColor }} fw-bold fs-2">{{ $initials }}</span>
                </span>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="fw-bold text-gray-900 mb-0">{{ $user->name }}</h2>
                        <span class="badge badge-light-{{ $roleColor }}">{{ $user->role?->name ?? 'Belum ada role' }}</span>
                        <span class="badge badge-light-{{ $statusColor }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                    </div>
                    <div class="fs-7 text-muted">{{ $user->email }}</div>
                    <div class="fs-8 text-muted font-monospace">{{ $user->uuid ?? 'ID: '.$user->id }}</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-light-{{ $isSsoLinked ? 'info' : 'warning' }}">{{ $isSsoLinked ? 'Terhubung SSO' : 'Menunggu SSO' }}</span>
                <span class="badge badge-light-secondary">Login SSO: {{ $user->last_sso_login_at?->diffForHumans() ?? 'belum login' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $akreditasiStats['total'] }}" label="Total Akreditasi" icon="ki-shield-search" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $akreditasiStats['active'] }}" label="Akreditasi Aktif" icon="ki-time" color="warning" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $akreditasiStats['completed'] }}" label="Selesai/Terminal" icon="ki-check" color="success" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $isSsoLinked ? 'Linked' : 'Pending' }}" label="Status SSO" icon="ki-verify" color="{{ $isSsoLinked ? 'info' : 'warning' }}" /></div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-5">
        <x-metronic.card title="Profil SSO Muhammadiyah ID" class="mb-8">
            <div class="d-grid gap-4 fs-7">
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">SSO ID</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ $user->sso_id ?: 'Belum tertaut' }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Muhammadiyah ID</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ $user->m_id ?: 'Belum diisi' }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">NBM</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ $user->nbm ?: 'Belum diisi' }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Level / Role SSO</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ trim(($user->sso_level ?: '-').' / '.($user->sso_role ?: '-')) }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Grup SSO</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ $ssoGroups->isNotEmpty() ? $ssoGroups->implode(', ') : 'Kosong' }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Last Login SSO</span>
                    <span class="fw-semibold text-gray-900 text-end">{{ $user->last_sso_login_at?->format('d M Y H:i') ?? 'Belum login' }}</span>
                </div>
            </div>
        </x-metronic.card>

        <x-metronic.card title="Audit Terbaru" class="mb-8">
            @if($auditLogs->isEmpty())
                <div class="text-muted fs-7">Belum ada audit log terkait user ini.</div>
            @else
                <div class="timeline-label">
                    @foreach($auditLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-label fw-semibold text-gray-800 fs-8">{{ $log->created_at?->format('d M H:i') }}</div>
                            <div class="timeline-badge">
                                <i class="ki-outline ki-abstract-8 text-primary fs-3"></i>
                            </div>
                            <div class="timeline-content fw-semibold text-gray-800 ps-3">
                                {{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}
                                @if($log->reason)
                                    <div class="fs-8 text-muted">{{ $log->reason }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-metronic.card>
    </div>

    <div class="col-xl-7">
        <x-metronic.card title="Role dan Status Akun" class="mb-8">
            <form method="POST"
                  action="{{ route('superadmin.master-data.users.update', $user) }}"
                  class="row g-5"
                  data-swal-confirm="true"
                  data-swal-title="Simpan perubahan akun?"
                  data-swal-text="Role atau status akun akan diperbarui."
                  data-swal-icon="warning"
                  data-swal-confirm-button="Ya, simpan">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="role_id" class="form-label required">Role</label>
                    <select id="role_id" name="role_id" class="form-select form-select-solid" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label required">Status</label>
                    <select id="status" name="status" class="form-select form-select-solid" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label for="access_reason" class="form-label required">Alasan Perubahan</label>
                    <textarea id="access_reason" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan perubahan role/status">{{ old('reason') }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Simpan Role/Status</button>
                </div>
            </form>
        </x-metronic.card>

        <x-metronic.card title="Identitas Pre-registration SSO" class="mb-8">
            <form method="POST"
                  action="{{ route('superadmin.master-data.users.sso-identity.update', $user) }}"
                  class="row g-5"
                  data-swal-confirm="true"
                  data-swal-title="Simpan identitas SSO?"
                  data-swal-text="Data ini dipakai untuk pencocokan akun saat callback Muhammadiyah ID."
                  data-swal-icon="warning"
                  data-swal-confirm-button="Ya, simpan">
                @csrf
                @method('PATCH')
                <div class="col-md-6">
                    <label for="m_id" class="form-label">Muhammadiyah ID</label>
                    <input id="m_id" type="text" name="m_id" value="{{ old('m_id', $user->m_id) }}" class="form-control form-control-solid" placeholder="0000 0000 0000 0000">
                </div>
                <div class="col-md-6">
                    <label for="nbm" class="form-label">NBM</label>
                    <input id="nbm" type="text" name="nbm" value="{{ old('nbm', $user->nbm) }}" class="form-control form-control-solid" placeholder="Contoh: 123456">
                </div>
                <div class="col-12">
                    <label for="sso_reason" class="form-label required">Alasan Perubahan</label>
                    <textarea id="sso_reason" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan update identitas SSO">{{ old('reason') }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Simpan Identitas SSO</button>
                </div>
            </form>
        </x-metronic.card>

        <x-metronic.card title="Reset Tautan SSO">
            <x-metronic.alert type="{{ $isSsoLinked ? 'warning' : 'info' }}">
                <div class="fw-semibold mb-1">{{ $isSsoLinked ? 'Akun ini sudah tertaut ke SSO.' : 'Akun ini belum tertaut ke SSO.' }}</div>
                <div>Reset tautan akan menghapus `sso_id`, level, role, grup, dan waktu login SSO terakhir. M-ID dan NBM tetap dipertahankan untuk pencocokan ulang.</div>
            </x-metronic.alert>

            <form method="POST"
                  action="{{ route('superadmin.master-data.users.sso-link.destroy', $user) }}"
                  data-swal-confirm="true"
                  data-swal-title="Reset tautan SSO?"
                  data-swal-text="User harus login ulang Muhammadiyah ID untuk menautkan kembali akun ini."
                  data-swal-icon="warning"
                  data-swal-confirm-button="Ya, reset">
                @csrf
                @method('DELETE')
                <label for="unlink_reason" class="form-label required">Alasan Reset</label>
                <textarea id="unlink_reason" name="reason" class="form-control form-control-solid mb-5" rows="3" required placeholder="Jelaskan alasan reset tautan SSO">{{ old('reason') }}</textarea>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-danger" @disabled(! $isSsoLinked)>Reset Tautan SSO</button>
                </div>
            </form>
        </x-metronic.card>
    </div>
</div>
@endsection
