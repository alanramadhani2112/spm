@extends('layouts.metronic.app')

@section('title', 'Super Admin — Visitasi')
@section('pageTitle', 'Visitasi')

@section('toolbar')
<a href="{{ route('superadmin.akreditasi.index', ['period' => $period]) }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
@endsection

@section('breadcrumbs')
    <x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Visitasi', 'active' => true]]" />
@endsection

@section('content')
@php use App\Models\Akreditasi; @endphp



<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        <p class="fs-7 text-muted mb-0">Pantau jadwal visitasi, item yang lewat jadwal, dan pengajuan yang siap divalidasi akhir.</p>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-6">
    <div class="col-md-4"><x-metronic.stat-card value="{{ $summary['ready'] }}" label="Siap Dijadwalkan" icon="ki-calendar-add" color="warning" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $summary['scheduled'] }}" label="Terjadwal" icon="ki-calendar-tick" color="info" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $summary['ongoing'] }}" label="Sedang Berjalan" icon="ki-calendar-2" color="primary" /></div>
</div>

<x-metronic.card title="Daftar Visitasi" flush>
    <x-slot name="header">
        <form method="GET" action="{{ route('superadmin.visitasi.index') }}" class="d-flex flex-wrap align-items-center gap-3 w-100">
            <select name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                @foreach(array_merge(['all' => 'Semua Periode'], $periodOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                @foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(($status ?? 'all') == $value)>{{ $label }}</option>@endforeach
            </select>
            <select name="schedule" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all" @selected(($schedule ?? 'all') === 'all')>Semua Jadwal</option>
                @foreach($scheduleOptions as $value => $label)<option value="{{ $value }}" @selected(($schedule ?? 'all') == $value)>{{ $label }}</option>@endforeach
            </select>
            <div class="input-group input-group-sm w-250px">
                <span class="input-group-text"><i class="ki-outline ki-magnifier fs-4"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari..." value="{{ $search ?? '' }}">
            </div>
            <button type="submit" class="btn btn-sm btn-light">Cari</button>
        </form>
    </x-slot>

    <div class="table-responsive">
        <table class="table align-middle table-row-dashed table-striped fs-6 gy-4">
            <thead><tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0"><th>Pesantren</th><th>Status</th><th>Jadwal</th><th>Asesor</th><th class="text-end">Aksi</th></tr></thead>
            <tbody class="text-gray-600 fw-semibold">
                @forelse($visitasiRows as $row)
                    @php $a = $row['akreditasi']; @endphp
                    <tr>
                        <td><div class="fw-semibold text-gray-900">{{ $a->user?->pesantren?->nama_pesantren ?? $a->user?->name ?? '—' }}</div><div class="fs-8 text-muted">{{ $a->uuid }}</div></td>
                        <td><span class="badge badge-light-{{ $row['status_color'] ?? 'secondary' }}">{{ $row['status_label'] }}</span></td>
                        <td>{{ $row['jadwal'] ?? '—' }}</td>
                        <td>{{ $row['asesor'] ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('superadmin.akreditasi.show', $a) }}" class="btn btn-sm btn-light">Detail</a>
                            @foreach($row['actions'] as $action)
                                <a href="{{ $action['route'] }}" class="btn btn-sm btn-light-{{ $action['color'] }} ms-1">{{ $action['label'] }}</a>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-metronic.empty-state icon="ki-calendar-tick" title="Belum ada data visitasi" description="Data akan muncul setelah visitasi dijadwalkan." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection
