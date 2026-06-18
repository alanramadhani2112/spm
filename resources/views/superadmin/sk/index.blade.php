@extends('layouts.metronic.app')

@section('title', 'Super Admin — SK Management')
@section('pageTitle', 'SK Management')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-document fs-2"></i>Workflow Console
    </a>
    <a href="{{ route('superadmin.sk.export', array_filter(['period' => $period, 'status' => $status, 'certificate' => $certificate, 'q' => $search], fn($value) => filled($value))) }}" class="btn btn-sm btn-light-primary">
        <i class="ki-outline ki-exit-up fs-2"></i>Export CSV
    </a>
</div>
@endsection

@section('content')
@php use App\Models\Akreditasi; @endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">SK Management</h2>
        <p class="fs-7 text-muted mb-0">Pantau pengajuan siap terbit SK, SK yang sudah terbit, masa berlaku, dan sertifikat digital.</p>
    </div>
    <span class="badge badge-light-primary">{{ $skRows->count() }} item ditampilkan</span>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-6"><x-metronic.stat-card value="{{ $stats['ready'] }}" label="Siap Terbit" icon="ki-medal-star" color="warning" /></div>
    <div class="col-xl col-md-6"><x-metronic.stat-card value="{{ $stats['published'] }}" label="SK Terbit" icon="ki-check-circle" color="success" /></div>
    <div class="col-xl col-md-6"><x-metronic.stat-card value="{{ $stats['certificate'] }}" label="Sertifikat Digital" icon="ki-file-added" color="primary" /></div>
    <div class="col-xl col-md-6"><x-metronic.stat-card value="{{ $stats['expired'] }}" label="Kedaluwarsa" icon="ki-warning" color="danger" /></div>
</div>

<x-metronic.card title="Filter & Daftar SK" flush>
    <x-slot:header>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($period !== 'all')<span class="badge badge-light-info">Periode: {{ $period }}</span>@endif
            @if($status !== 'all')<span class="badge badge-light-warning">Status: {{ $statusOptions[$status] ?? $status }}</span>@endif
            @if($certificate !== 'all')<span class="badge badge-light-primary">Sertifikat: {{ $certificateOptions[$certificate] ?? $certificate }}</span>@endif
            @if($search !== '')<span class="badge badge-light-success">Cari: {{ $search }}</span>@endif
        </div>
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.sk.index') }}" class="row g-3 align-items-end mb-8">
        <div class="col-lg-2 col-md-6">
            <label class="form-label" for="filter_sk_period">Periode</label>
            <select id="filter_sk_period" name="period" class="form-select form-select-solid">
                @foreach($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label" for="filter_sk_status">Status SK</label>
            <select id="filter_sk_status" name="status" class="form-select form-select-solid">
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label" for="filter_sk_certificate">Sertifikat</label>
            <select id="filter_sk_certificate" name="certificate" class="form-select form-select-solid">
                @foreach($certificateOptions as $value => $label)
                    <option value="{{ $value }}" @selected($certificate === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-8">
            <label class="form-label" for="filter_sk_search">Cari</label>
            <input id="filter_sk_search" type="search" name="q" value="{{ $search }}" class="form-control form-control-solid" placeholder="UUID, nomor SK, pesantren, email...">
        </div>
        <div class="col-lg-1 col-md-4 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('superadmin.sk.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-260px">Pesantren</th>
                    <th class="min-w-160px">Status</th>
                    <th class="min-w-180px">Nomor SK</th>
                    <th class="min-w-150px">Nilai</th>
                    <th class="min-w-190px">Masa Berlaku</th>
                    <th class="min-w-160px">Sertifikat</th>
                    <th class="text-end min-w-170px pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skRows as $akreditasi)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren' }}</div>
                            <div class="text-muted fs-8">{{ $akreditasi->user?->email ?? '—' }}</div>
                            <div class="text-muted fs-8 font-monospace">{{ \Illuminate\Support\Str::limit($akreditasi->uuid, 28) }}</div>
                        </td>
                        <td>
                            <span class="badge badge-light-{{ $akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED ? 'warning' : 'success' }}">{{ $akreditasi->getStatusLabel() }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->nomor_sk ?: 'Belum terbit' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</div>
                            <div class="fs-8 text-muted">Peringkat: {{ $akreditasi->peringkat ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fs-8 text-muted">Mulai</div>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->masa_berlaku?->format('d M Y') ?? '—' }}</div>
                            <div class="fs-8 text-muted mt-1">Akhir: {{ $akreditasi->masa_berlaku_akhir?->format('d M Y') ?? '—' }}</div>
                        </td>
                        <td>
                            @if($akreditasi->sertifikat_path)
                                <span class="badge badge-light-success mb-2">Tersedia</span>
                                <div><a href="{{ route('superadmin.akreditasi.sertifikat.download', $akreditasi) }}" class="fs-8 fw-semibold text-primary">Unduh Sertifikat</a></div>
                            @else
                                <span class="badge badge-light-secondary">Belum ada</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                    <a href="{{ route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi) }}" class="btn btn-sm btn-success">Terbitkan SK</a>
                                @endif
                                <a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="btn btn-sm btn-light">Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-muted border rounded bg-light">
                                Belum ada data SK yang cocok dengan filter.
                                <div class="mt-4">
                                    <a href="{{ route('superadmin.sk.index') }}" class="btn btn-sm btn-light">Reset Filter</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection
