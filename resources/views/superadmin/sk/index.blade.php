@extends('layouts.metronic.app')

@section('title', 'Super Admin — Manajemen SK')
@section('pageTitle', 'Manajemen SK')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    @if(auth()->user()?->hasPermission('superadmin.sk.export'))
        <a href="{{ route('superadmin.sk.export', array_filter(['period' => $period, 'status' => $status, 'certificate' => $certificate, 'q' => $search], fn($value) => filled($value))) }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-exit-up fs-2"></i>Ekspor CSV
        </a>
    @endif
    <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-warning">
        <i class="ki-outline ki-medal-star fs-3"></i>Siap Terbit
    </a>
</div>
@endsection

@section('content')
@php use App\Models\Akreditasi; $activeFilterCount = collect([$period !== 'all', $status !== 'all', $certificate !== 'all', $search !== ''])->filter()->count(); @endphp

<x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Manajemen SK', 'active' => true]]" />

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-1">Manajemen SK</h2>
        <p class="fs-7 text-muted mb-0">{{ $stats['displayed'] ?? $skRows->count() }} SK ditampilkan · {{ $stats['ready'] }} siap terbit · {{ $stats['expired'] }} kedaluwarsa</p>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-6">
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['ready'] }}" label="Siap Terbit" icon="ki-medal-star" color="warning" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['published'] }}" label="Sudah Terbit" icon="ki-check-circle" color="success" /></div>
    <div class="col-md-4"><x-metronic.stat-card value="{{ $stats['expired'] }}" label="Kedaluwarsa" icon="ki-warning" color="danger" /></div>
</div>

<x-metronic.card title="Daftar SK" flush>
    <x-slot name="header">
        <form method="GET" action="{{ route('superadmin.sk.index') }}" class="d-flex flex-wrap align-items-center gap-3 w-100">
            <select name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                @foreach(array_merge(['all' => 'Semua Periode'], $periodOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                @foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(($status ?? 'all') == $value)>{{ $label }}</option>@endforeach
            </select>
            <select name="certificate" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
                <option value="all" @selected(($certificate ?? 'all') === 'all')>Semua Sertifikat</option>
                @foreach($certificateOptions as $value => $label)<option value="{{ $value }}" @selected(($certificate ?? 'all') == $value)>{{ $label }}</option>@endforeach
            </select>
            <div class="input-group input-group-sm w-250px">
                <span class="input-group-text"><i class="ki-outline ki-magnifier fs-4"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-sm btn-light">Cari</button>
            </div>
            @if($activeFilterCount > 0)<a href="{{ route('superadmin.sk.index') }}" class="btn btn-sm btn-light-danger">Reset</a>@endif
        </form>
    </x-slot>

    <div class="table-responsive">
        <table class="table align-middle table-row-dashed table-striped fs-6 gy-4">
            <thead><tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0"><th>Pesantren</th><th>No. SK</th><th>Status</th><th>Nilai</th><th>Masa Berlaku</th><th>Sertifikat</th><th class="text-end">Aksi</th></tr></thead>
            <tbody class="text-gray-600 fw-semibold">
                @forelse($skRows as $row)
                    @php $akreditasi = $row; $isReady = ($row->status === \App\Models\Akreditasi::STATUS_FINAL_APPROVED) ?? false; $isExpired = ($row->masa_berlaku_akhir && \Carbon\Carbon::parse($row->masa_berlaku_akhir)->isPast()) ?? false; $isExpiringSoon = ($row->masa_berlaku_akhir && \Carbon\Carbon::parse($row->masa_berlaku_akhir)->diffInDays(now()) <= 60) ?? false; $expiryDate = $row['masa_berlaku'] ?? null; @endphp
                    <tr>
                        <td><div class="fw-semibold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '—' }}</div><div class="fs-8 text-muted">{{ $akreditasi->uuid }}</div></td>
                        <td><span class="text-gray-900">{{ $row['nomor_sk'] ?? '—' }}</span></td>
                        <td><span class="badge badge-light-{{ $isReady ? 'warning' : ($isExpired ? 'danger' : 'primary') }}">{{ $row['status_label'] }}</span></td>
                        <td><span class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</span><div class="fs-8 text-muted">{{ $akreditasi->peringkat ?? '—' }}</div></td>
                        <td><span class="fs-8">{{ $expiryDate?->format('d M Y') ?? '—' }}</span>@if($isExpired)<span class="badge badge-light-danger ms-1">Kedaluwarsa</span>@endif</td>
                        <td>@if($akreditasi->sertifikat_path)<span class="badge badge-light-success">Tersedia</span>@else<span class="badge badge-light-danger">Belum</span>@endif</td>
                        <td class="text-end">
                            <x-superadmin.action-menu label="Aksi SK {{ $akreditasi->uuid }}">
                                <div class="menu-item px-3"><a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2"><i class="ki-outline ki-eye fs-4"></i><span>Detail</span></a></div>
                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)<div class="menu-item px-3"><a href="{{ route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2 text-success"><i class="ki-outline ki-medal-star fs-4"></i><span>Terbitkan SK</span></a></div>@endif
                                @if($akreditasi->sertifikat_path)<div class="menu-item px-3"><a href="{{ route('superadmin.akreditasi.sertifikat.download', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2 text-primary"><i class="ki-outline ki-file-down fs-4"></i><span>Unduh Sertifikat</span></a></div>@endif
                            </x-superadmin.action-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-metronic.empty-state icon="ki-medal-star" title="Belum ada data SK" description="Coba reset filter atau fokus ke antrean siap terbit." :actionLabel="'Lihat Siap Terbit'" :actionRoute="route('superadmin.sk.index', ['status' => 'ready'])" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection

