@extends('layouts.metronic.app')

@section('title', 'Super Admin — Konsol Akreditasi')
@section('pageTitle', 'Konsol Akreditasi')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    @php $hasAnyExport = auth()->user()?->hasPermission('superadmin.akreditasi.export') || auth()->user()?->hasPermission('superadmin.akreditasi_scores.export') || auth()->user()?->hasPermission('superadmin.akreditasi_documents.export'); @endphp
    @if($hasAnyExport)
        <x-superadmin.action-menu label="Ekspor" buttonClass="btn-light" width="w-200px">
            @if(auth()->user()?->hasPermission('superadmin.akreditasi.export'))
                <div class="menu-item px-3">
                    <a href="{{ route('superadmin.akreditasi.export', request()->only(['period', 'status', 'q'])) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                        <i class="ki-outline ki-exit-up fs-4"></i><span>Ekspor CSV</span>
                    </a>
                </div>
            @endif
            @if(auth()->user()?->hasPermission('superadmin.akreditasi_scores.export'))
                <div class="menu-item px-3">
                    <a href="{{ route('superadmin.akreditasi.export-scores', request()->only(['period', 'status', 'q'])) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                        <i class="ki-outline ki-chart-line fs-4"></i><span>Ekspor Nilai</span>
                    </a>
                </div>
            @endif
            @if(auth()->user()?->hasPermission('superadmin.akreditasi_documents.export'))
                <div class="menu-item px-3">
                    <a href="{{ route('superadmin.akreditasi.export-documents', request()->only(['period', 'status', 'q'])) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                        <i class="ki-outline ki-document fs-4"></i><span>Ekspor Dokumen</span>
                    </a>
                </div>
            @endif
        </x-superadmin.action-menu>
    @endif
    <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-add-files fs-2"></i>Pengajuan Baru
    </a>
</div>
@endsection

@section('content')
@php use App\Models\Akreditasi; @endphp

<x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Konsol Akreditasi', 'active' => true]]" />

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-1">Konsol Akreditasi</h2>
        <p class="fs-7 text-muted mb-0">{{ $akreditasis->count() }} pengajuan ditampilkan
            @if(($status ?? 'all') !== 'all') · Fokus: {{ $statusOptions[$status] ?? $status }} @endif
            @if(($search ?? '') !== '') · Pencarian: {{ $search }} @endif
        </p>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-6">
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['total'] ?? $akreditasis->count() }}" label="Total" icon="ki-document" color="primary" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['active'] ?? 0 }}" label="Aktif" icon="ki-timer" color="warning" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['overdue'] ?? 0 }}" label="Overdue" icon="ki-warning" color="danger" /></div>
</div>

<x-metronic.card title="Daftar Pengajuan" flush>
    <x-slot name="header">
        <form method="GET" action="{{ route('superadmin.akreditasi.index') }}" class="d-flex flex-wrap align-items-center gap-3 w-100">
            <select name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                @foreach(array_merge(['all' => 'Semua Periode'], $periodOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($status ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="input-group input-group-sm w-250px">
                <span class="input-group-text"><i class="ki-outline ki-magnifier fs-4"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-sm btn-light">Cari</button>
            </div>
            @if(($status ?? 'all') !== 'all' || ($period ?? 'all') !== 'all' || ($search ?? '') !== '')
                <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light-danger">Reset</a>
            @endif
        </form>
    </x-slot>

    <div class="table-responsive">
        <table class="table align-middle table-row-dashed table-striped fs-6 gy-4">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th>Pengajuan</th>
                    <th>Pesantren</th>
                    <th>Status</th>
                    <th>Nilai</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @forelse($akreditasis as $akreditasi)
                    @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; $stepLabel = $akreditasi->getStatusLabel() ?? $akreditasi->status; @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->uuid }}</div>
                            <div class="fs-8 text-muted">{{ $akreditasi->created_at->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '—' }}</div>
                        </td>
                        <td><span class="badge badge-light-{{ $color }}">{{ $stepLabel }}</span></td>
                        <td>
                            <span class="fw-bold text-gray-900">{{ $akreditasi->nilai_akhir ?? '—' }}</span>
                            <div class="fs-8 text-muted">{{ $akreditasi->peringkat ?? '' }}</div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                @php
                                    $primaryAction = $primaryActionsById[$akreditasi->id] ?? null;
                                    $secondaryActions = $secondaryActionsById[$akreditasi->id] ?? [];
                                @endphp
                                @if($primaryAction)
                                    <a href="{{ $primaryAction['route'] }}"
                                       class="btn btn-sm btn-{{ $primaryAction['color'] === 'warning' ? 'warning' : ($primaryAction['color'] === 'danger' ? 'danger' : ($primaryAction['color'] === 'success' ? 'success' : 'primary')) }}"
                                       data-swal-confirm="true"
                                       data-swal-title="Buka {{ $primaryAction['label'] }}?"
                                       data-swal-text="Lanjutkan ke halaman {{ $primaryAction['label'] }} untuk {{ $akreditasi->uuid }}."
                                       data-swal-icon="question"
                                       data-swal-confirm-button="Ya, buka">{{ $primaryAction['label'] }}</a>
                                @endif
                                <a href="{{ route('superadmin.akreditasi.show', $akreditasi->id) }}" class="btn btn-sm btn-light">Detail</a>
                                @if(! empty($secondaryActions))
                                    <x-superadmin.action-menu label="Tindakan Lain {{ $akreditasi->uuid }}">
                                        @foreach($secondaryActions as $action)
                                            <div class="menu-item px-3">
                                                <a href="{{ $action['route'] }}"
                                                   class="menu-link px-3 d-flex align-items-center gap-2 text-{{ $action['color'] }}"
                                                   data-swal-confirm="true"
                                                   data-swal-title="Buka {{ $action['label'] }}?"
                                                   data-swal-text="Lanjutkan ke halaman {{ $action['label'] }} untuk {{ $akreditasi->uuid }}."
                                                   data-swal-icon="question"
                                                   data-swal-confirm-button="Ya, buka">
                                                    <i class="ki-outline ki-right-square fs-4"></i>
                                                    <span>{{ $action['label'] }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </x-superadmin.action-menu>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-metronic.empty-state
                                icon="ki-document"
                                title="Belum ada pengajuan"
                                description="Belum ada akreditasi yang cocok dengan filter. Coba reset filter atau buat pengajuan baru."
                                :actionLabel="'Buat Pengajuan'"
                                :actionRoute="route('superadmin.akreditasi.pengajuan')" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection

