@extends('layouts.metronic.app')

@section('title', 'Audit Log')
@section('pageTitle', 'Audit Log')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Jejak Aktivitas Super Admin</h2>
        <p class="fs-7 text-muted mb-0">Telusuri perubahan status, setting, dan aksi operasional yang tercatat pada audit trail.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.audit.export', request()->only(['actor', 'action', 'start_date', 'end_date'])) }}" class="btn btn-sm btn-light-success">
            <i class="ki-outline ki-exit-down fs-3"></i>Export CSV
        </a>
        <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-document fs-3"></i>Workflow Console
        </a>
        <a href="{{ route('superadmin.settings.index') }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-setting-2 fs-3"></i>Settings
        </a>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['total'] ?? $logs->total() }}" label="Log Terfilter" icon="ki-time" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['action_types'] ?? 0 }}" label="Tipe Aksi" icon="ki-category" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['actors'] ?? 0 }}" label="Aktor" icon="ki-profile-user" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['today'] ?? 0 }}" label="Hari Ini" icon="ki-calendar-tick" color="warning" /></div>
</div>

<x-metronic.card title="Filter Audit" class="mb-8">
    <x-slot:header>
        @if($hasFilters ?? false)
            <span class="badge badge-light-primary">Filter aktif</span>
        @else
            <span class="badge badge-light-secondary">Semua log</span>
        @endif
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.audit.index') }}" class="row g-5 align-items-end">
        <div class="col-lg-3 col-md-6">
            <x-metronic.form-input name="actor" label="ID Aktor" placeholder="Contoh: 1" :value="request('actor')" />
        </div>
        <div class="col-lg-3 col-md-6">
            <x-metronic.form-input name="action" label="Tindakan" type="select" :value="request('action')" :options="$actionOptions ?? ['' => 'Semua']" />
        </div>
        <div class="col-lg-2 col-md-6">
            <x-metronic.form-input name="start_date" label="Dari Tanggal" type="date" :value="request('start_date')" />
        </div>
        <div class="col-lg-2 col-md-6">
            <x-metronic.form-input name="end_date" label="Sampai Tanggal" type="date" :value="request('end_date')" />
        </div>
        <div class="col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="ki-outline ki-filter fs-4"></i>Filter
            </button>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-light" aria-label="Reset filter audit">Reset</a>
        </div>
    </form>
</x-metronic.card>

<x-metronic.card title="Daftar Audit Trail" flush>
    <x-slot:header>
        <span class="badge badge-light-primary">{{ $logs->total() }} log</span>
    </x-slot:header>

    @if($logs->isEmpty())
        <div class="text-center py-12 text-muted border rounded bg-light">
            <i class="ki-outline ki-search-list fs-2x text-gray-400 mb-3"></i>
            <div class="fw-semibold text-gray-800 mb-1">Tidak ada log audit yang cocok.</div>
            <div class="fs-7 mb-4">Coba ubah filter atau reset untuk melihat seluruh audit trail.</div>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-light-primary">Reset Filter</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0 bg-light">
                        <th class="ps-4 min-w-170px">Waktu</th>
                        <th class="min-w-220px">Aktor</th>
                        <th class="min-w-220px">Tindakan</th>
                        <th class="min-w-190px">Akreditasi</th>
                        <th class="text-end min-w-90px pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($logs as $log)
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <div class="fw-bold text-gray-900">{{ $log->created_at?->format('d M Y') }}</div>
                                <div class="fs-8 text-muted">{{ $log->created_at?->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-gray-900">{{ $log->user?->name ?? 'Aktor tidak diketahui' }}</div>
                                <div class="fs-8 text-muted">ID: {{ $log->user_id ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-primary">{{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}</span>
                                <div class="fs-8 text-muted font-monospace mt-1">{{ $log->action_type }}</div>
                            </td>
                            <td>
                                <div class="font-monospace text-gray-800">{{ $log->akreditasi?->uuid ? \Illuminate\Support\Str::limit($log->akreditasi->uuid, 16, '...') : '—' }}</div>
                                <div class="fs-8 text-muted">ID: {{ $log->akreditasi_id ?? '—' }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <x-superadmin.action-menu label="Buka aksi audit log {{ $log->id }}">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('superadmin.audit.show', $log->id) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                            <i class="ki-outline ki-eye fs-4"></i>
                                            <span>Lihat Detail</span>
                                        </a>
                                    </div>
                                    @if($log->akreditasi_id)
                                        <div class="menu-item px-3">
                                            <a href="{{ route('superadmin.akreditasi.show', $log->akreditasi_id) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-document fs-4"></i>
                                                <span>Buka Akreditasi</span>
                                            </a>
                                        </div>
                                    @endif
                                </x-superadmin.action-menu>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-top border-gray-200 pt-6">
            {{ $logs->links() }}
        </div>
    @endif
</x-metronic.card>
@endsection
