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
    <div class="mw-lg-600px">
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">SK Management</h2>
        <p class="fs-7 text-muted mb-3">Pantau pengajuan siap terbit SK, SK yang sudah terbit, masa berlaku, dan sertifikat digital.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-light-warning">
                <i class="ki-outline ki-medal-star fs-4"></i>Lihat Siap Terbit
            </a>
            <a href="{{ route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_FINAL_APPROVED]) }}" class="btn btn-sm btn-light-success">
                <i class="ki-outline ki-document fs-4"></i>Buka Antrian Publish
            </a>
        </div>
    </div>
    <div class="d-flex flex-column align-items-xl-end gap-2">
        <span class="badge badge-light-primary">{{ $skRows->count() }} item ditampilkan</span>
        <span class="fs-8 text-muted text-xl-end">Gunakan halaman ini untuk menerbitkan SK, memantau sertifikat, dan meninjau masa berlaku.</span>
    </div>
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
        @if(($stats['ready'] ?? 0) > 0)
            <div class="rounded border border-warning border-dashed bg-light-warning p-4 mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-bold text-gray-900 mb-1">Antrian siap terbit perlu perhatian</div>
                        <div class="fs-7 text-gray-700">{{ $stats['ready'] }} pengajuan sudah final approved dan menunggu penerbitan SK.</div>
                    </div>
                    <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-warning">Fokus Siap Terbit</a>
                </div>
            </div>
        @endif
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
                            <div class="d-flex flex-column gap-2">
                                <span class="badge badge-light-{{ $akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED ? 'warning' : 'success' }} align-self-start">{{ $akreditasi->getStatusLabel() }}</span>
                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                    <span class="fs-8 fw-semibold text-warning">Perlu diterbitkan sekarang</span>
                                @elseif(blank($akreditasi->sertifikat_path))
                                    <span class="fs-8 fw-semibold text-danger">Belum ada sertifikat digital</span>
                                @else
                                    <span class="fs-8 text-muted">SK sudah terdokumentasi</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->nomor_sk ?: 'Belum terbit' }}</div>
                            <div class="fs-8 text-muted mt-1">UUID: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 18) }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</div>
                            <div class="fs-8 text-muted">Peringkat: {{ $akreditasi->peringkat ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fs-8 text-muted">Mulai</div>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->masa_berlaku?->format('d M Y') ?? '—' }}</div>
                            @php
                                $expiryDate = $akreditasi->masa_berlaku_akhir;
                                $isExpired = $expiryDate && $expiryDate->isPast();
                                $isExpiringSoon = $expiryDate && ! $isExpired && now()->diffInDays($expiryDate, false) <= 60;
                            @endphp
                            <div class="fs-8 mt-1 {{ $isExpired ? 'text-danger fw-bold' : ($isExpiringSoon ? 'text-warning fw-semibold' : 'text-muted') }}">
                                Akhir: {{ $expiryDate?->format('d M Y') ?? '—' }}
                                @if($isExpired)
                                    · Kedaluwarsa
                                @elseif($isExpiringSoon)
                                    · Segera berakhir
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($akreditasi->sertifikat_path)
                                <span class="badge badge-light-success mb-2">Tersedia</span>
                                <div><a href="{{ route('superadmin.akreditasi.sertifikat.download', $akreditasi) }}" class="fs-8 fw-semibold text-primary">Unduh Sertifikat</a></div>
                            @else
                                <span class="badge badge-light-danger mb-2">Belum tersedia</span>
                                <div class="fs-8 text-muted">Terbitkan atau unggah sertifikat setelah publish SK.</div>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex flex-column align-items-end gap-2">
                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                    <a href="{{ route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi) }}" class="btn btn-sm btn-success w-150px">Terbitkan SK</a>
                                @endif
                                <a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="btn btn-sm btn-light w-150px">Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-muted border rounded bg-light">
                                <div class="fw-bold text-gray-900 mb-2">Belum ada data SK yang cocok dengan filter.</div>
                                <div class="fs-7 text-muted">Coba reset filter, buka seluruh antrian siap terbit, atau kembali ke workflow console untuk mencari pengajuan final approved.</div>
                                <div class="mt-4 d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('superadmin.sk.index') }}" class="btn btn-sm btn-light">Reset Filter</a>
                                    <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-light-warning">Lihat Siap Terbit</a>
                                    <a href="{{ route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_FINAL_APPROVED]) }}" class="btn btn-sm btn-primary">Buka Workflow Console</a>
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
