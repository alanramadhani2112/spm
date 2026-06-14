@extends('layouts.metronic.app')

@section('title', 'Data Pesantren')
@section('pageTitle', 'Data Pesantren')

@section('toolbar')
<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-add-files fs-3"></i>Ajukan Akreditasi
    </a>
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-left fs-4"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="card card-flush bg-light-info border border-info border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <span class="symbol symbol-45px flex-shrink-0">
                    <span class="symbol-label bg-info"><i class="ki-outline ki-home fs-2 text-white"></i></span>
                </span>
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Control Center Data Pesantren</h2>
                    <div class="fs-7 text-muted">Pantau kesiapan profil, data assessment, dan status lock sebelum atau sesudah pengajuan akreditasi.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-light-info">{{ $rows->count() }} ditampilkan</span>
                @if($hasFilters)
                    <span class="badge badge-light-warning">Filter aktif</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['total'] ?? 0 }}" label="Total Pesantren" icon="ki-home" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['ready'] ?? 0 }}" label="Assessment Ready" icon="ki-shield-tick" color="success" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['incomplete'] ?? 0 }}" label="Belum Lengkap" icon="ki-warning-2" color="warning" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['locked'] ?? 0 }}" label="Terkunci" icon="ki-lock" color="danger" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['unlocked'] ?? 0 }}" label="Terbuka" icon="ki-unlock" color="info" /></div>
</div>

<x-metronic.card title="Filter Data Pesantren" class="mb-8">
    <x-slot:header>
        @if($hasFilters)
            <span class="badge badge-light-primary">Hasil: {{ $rows->count() }} pesantren</span>
        @else
            <span class="badge badge-light-secondary">Semua pesantren</span>
        @endif
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.master-data.pesantren.index') }}" class="row g-5 align-items-end">
        <div class="col-xl-4 col-md-6">
            <label for="q" class="form-label">Cari Pesantren</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-solid" placeholder="Nama, NSP, email, atau user">
        </div>
        <div class="col-xl-3 col-md-6">
            <label for="readiness" class="form-label">Kesiapan</label>
            <select id="readiness" name="readiness" class="form-select form-select-solid">
                <option value="">Semua</option>
                <option value="ready" @selected(($filters['readiness'] ?? '') === 'ready')>Assessment ready</option>
                <option value="incomplete" @selected(($filters['readiness'] ?? '') === 'incomplete')>Belum lengkap</option>
            </select>
        </div>
        <div class="col-xl-3 col-md-6">
            <label for="lock" class="form-label">Lock</label>
            <select id="lock" name="lock" class="form-select form-select-solid">
                <option value="">Semua</option>
                <option value="locked" @selected(($filters['lock'] ?? '') === 'locked')>Terkunci</option>
                <option value="unlocked" @selected(($filters['lock'] ?? '') === 'unlocked')>Terbuka</option>
            </select>
        </div>
        <div class="col-xl-2 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="ki-outline ki-filter fs-4"></i>Filter
            </button>
            <a href="{{ route('superadmin.master-data.pesantren.index') }}" class="btn btn-light" aria-label="Reset filter data pesantren">Reset</a>
        </div>
    </form>
</x-metronic.card>

<x-metronic.card title="Daftar Pesantren" flush>
    <x-slot:header>
        <span class="badge badge-light-primary">{{ $rows->count() }} pesantren</span>
    </x-slot:header>

    @if($rows->isEmpty())
        <div class="text-center py-12 text-muted border rounded bg-light">
            <i class="ki-outline ki-search-list fs-2x text-gray-400 mb-3"></i>
            <div class="fw-semibold text-gray-800 mb-1">Tidak ada pesantren yang cocok.</div>
            <div class="fs-7 mb-4">Coba ubah filter atau reset untuk melihat seluruh data pesantren.</div>
            <a href="{{ route('superadmin.master-data.pesantren.index') }}" class="btn btn-sm btn-light-primary">Reset Filter</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0 bg-light">
                        <th class="ps-4 min-w-260px">Pesantren</th>
                        <th class="min-w-180px">Kesiapan</th>
                        <th class="min-w-160px">Lock</th>
                        <th class="min-w-150px">Akreditasi Aktif</th>
                        <th class="text-end min-w-90px pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($rows as $row)
                        @php
                            $pesantren = $row['pesantren'];
                            $completeness = $row['completeness'];
                            $ready = (bool) $completeness['assessmentReady'];
                            $missingFields = $completeness['missingFields'] ?? [];
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-gray-900">{{ $pesantren->nama_pesantren }}</div>
                                <div class="fs-8 text-muted">{{ $pesantren->user?->name ?? 'User tidak ditemukan' }} - {{ $pesantren->user?->email ?? 'email kosong' }}</div>
                                <div class="fs-8 text-muted">NSP: {{ $pesantren->ns_pesantren ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $ready ? 'success' : 'warning' }}">{{ $ready ? 'Assessment Ready' : 'Belum Lengkap' }}</span>
                                <div class="fs-8 text-muted mt-1">
                                    Profil: {{ $completeness['profilMinimum'] ? 'lengkap' : 'kurang' }} - Unit: {{ $pesantren->units->count() }}
                                </div>
                                @if($missingFields)
                                    <div class="fs-8 text-muted">Missing: {{ implode(', ', $missingFields) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $pesantren->is_locked ? 'danger' : 'info' }}">{{ $pesantren->is_locked ? 'Terkunci' : 'Terbuka' }}</span>
                                <div class="fs-8 text-muted mt-1">{{ $pesantren->is_locked ? 'Pesantren tidak bisa edit profil' : 'Pesantren masih bisa edit profil' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $row['active_akreditasi_count'] > 0 ? 'primary' : 'secondary' }}">{{ $row['active_akreditasi_count'] }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <x-superadmin.action-menu label="Buka aksi data pesantren {{ $pesantren->nama_pesantren }}">
                                    <div class="menu-item px-3">
                                        <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#toggle-lock-{{ $pesantren->id }}">
                                            <i class="ki-outline {{ $pesantren->is_locked ? 'ki-unlock' : 'ki-lock' }} fs-4"></i>
                                            <span>{{ $pesantren->is_locked ? 'Buka Lock' : 'Kunci Data' }}</span>
                                        </button>
                                    </div>
                                </x-superadmin.action-menu>

                                <x-metronic.modal id="toggle-lock-{{ $pesantren->id }}" title="{{ $pesantren->is_locked ? 'Buka Lock Data Pesantren' : 'Kunci Data Pesantren' }}" size="md">
                                    <form id="toggle-lock-form-{{ $pesantren->id }}"
                                          method="POST"
                                          action="{{ route('superadmin.master-data.pesantren.toggle-lock', $pesantren) }}"
                                          class="d-grid gap-4 text-start"
                                          data-swal-confirm="true"
                                          data-swal-title="{{ $pesantren->is_locked ? 'Buka lock data?' : 'Kunci data pesantren?' }}"
                                          data-swal-text="Perubahan lock akan tercatat di audit log."
                                          data-swal-icon="warning"
                                          data-swal-confirm-button="Ya, lanjutkan"
                                          data-swal-confirm-class="btn btn-primary">
                                        @csrf @method('PATCH')

                                        <div class="rounded bg-light p-4">
                                            <div class="fw-bold text-gray-900">{{ $pesantren->nama_pesantren }}</div>
                                            <div class="fs-8 text-muted">{{ $pesantren->user?->email ?? 'email kosong' }}</div>
                                        </div>
                                        <div>
                                            <label for="lock_reason_{{ $pesantren->id }}" class="form-label required">Alasan</label>
                                            <textarea id="lock_reason_{{ $pesantren->id }}" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan lock/unlock data pesantren"></textarea>
                                            <div class="fs-8 text-muted mt-1">Alasan akan tersimpan di audit log.</div>
                                        </div>
                                    </form>
                                    <x-slot:footer>
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" form="toggle-lock-form-{{ $pesantren->id }}" class="btn btn-primary">Simpan</button>
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
