@extends('layouts.metronic.app')

@section('title', 'Hasil Akhir Akreditasi')
@section('pageTitle', 'Hasil Akhir Akreditasi')

@section('content')
@php
    use App\Models\Akreditasi;

    $peringkatLabels = [
        'A' => 'Unggul',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    $peringkatColors = [
        'A' => 'green',
        'B' => 'blue',
        'C' => 'yellow',
        'D' => 'red',
    ];

    $colorKey = $peringkatColors[$akreditasi->peringkat] ?? 'gray';

    $peringkatClasses = [
        'green' => 'bg-light-success text-success',
        'blue' => 'bg-light-primary text-primary',
        'yellow' => 'bg-light-warning text-warning',
        'red' => 'bg-light-danger text-danger',
        'gray' => 'bg-light text-gray-700',
    ];

    $peringkatIconColors = [
        'green' => 'text-success',
        'blue' => 'text-primary',
        'yellow' => 'text-warning',
        'red' => 'text-danger',
        'gray' => 'text-gray-500',
    ];

    $badgeMap = [
        'green' => 'success',
        'blue' => 'primary',
        'yellow' => 'warning',
        'red' => 'danger',
        'gray' => 'secondary',
    ];
@endphp

<div class="mx-auto">
    <div class="mb-8 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Hasil Akhir Akreditasi</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
            </p>
        </div>
        <x-metronic.badge type="{{ $badgeMap[$colorKey] }}" :label="$akreditasi->getStatusLabel()" />
    </div>

    @if($akreditasi->status === Akreditasi::STATUS_FINAL_REJECTED)
        <x-metronic.alert type="danger" dismissible>
            <span class="fw-bold">Akreditasi Ditolak</span>
            <span class="d-block mt-1">Akreditasi pesantren Anda tidak disetujui. Anda dapat mengajukan banding atau memulai pengajuan baru.</span>
        </x-metronic.alert>
    @endif

    @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED || $akreditasi->status === Akreditasi::STATUS_COMPLETED)
        <x-metronic.alert type="success" dismissible>
            <span class="fw-bold">Akreditasi Disetujui</span>
            <span class="d-block mt-1">Selamat! Akreditasi pesantren Anda telah disetujui.</span>
        </x-metronic.alert>
    @endif

    <div class="row g-6">
        {{-- Main Content --}}
        <div class="col-lg-8 d-grid gap-6">
            {{-- SK & Validity --}}
            <x-metronic.card title="Surat Keputusan">
                <div class="row g-4">
                    <div class="col-md-6">
                        <dt class="fs-8 fw-medium text-uppercase text-gray-500">Nomor SK</dt>
                        <dd class="mt-1 fs-7 fw-medium text-gray-900">
                            {{ $akreditasi->nomor_sk ?? '—' }}
                        </dd>
                    </div>
                    <div class="col-md-6">
                        <dt class="fs-8 fw-medium text-uppercase text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <x-metronic.badge type="{{ $badgeMap[$colorKey] }}" :label="$akreditasi->getStatusLabel()" />
                        </dd>
                    </div>
                    <div class="col-md-6">
                        <dt class="fs-8 fw-medium text-uppercase text-gray-500">Masa Berlaku Mulai</dt>
                        <dd class="mt-1 fs-7 text-gray-900">
                            {{ $akreditasi->masa_berlaku ? $akreditasi->masa_berlaku->format('d M Y') : '—' }}
                        </dd>
                    </div>
                    <div class="col-md-6">
                        <dt class="fs-8 fw-medium text-uppercase text-gray-500">Masa Berlaku Akhir</dt>
                        <dd class="mt-1 fs-7 text-gray-900">
                            {{ $akreditasi->masa_berlaku_akhir ? $akreditasi->masa_berlaku_akhir->format('d M Y') : '—' }}
                        </dd>
                    </div>
                </div>
            </x-metronic.card>

            {{-- Scores --}}
            <x-metronic.card title="Rincian Nilai">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="rounded border border-gray-200 p-4 text-center">
                            <dt class="fs-8 fw-medium text-uppercase text-gray-500">NA 1</dt>
                            <dd class="mt-2 fs-2 fw-bold text-gray-900">
                                {{ $akreditasi->na1 !== null ? number_format($akreditasi->na1, 2) : '—' }}
                            </dd>
                            @if($akreditasi->is_na1_final)
                                <x-metronic.badge type="success" label="Final" class="mt-1" />
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded border border-gray-200 p-4 text-center">
                            <dt class="fs-8 fw-medium text-uppercase text-gray-500">NA 2</dt>
                            <dd class="mt-2 fs-2 fw-bold text-gray-900">
                                {{ $akreditasi->na2 !== null ? number_format($akreditasi->na2, 2) : '—' }}
                            </dd>
                            @if($akreditasi->is_na2_final)
                                <x-metronic.badge type="success" label="Final" class="mt-1" />
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded border border-gray-200 p-4 text-center">
                            <dt class="fs-8 fw-medium text-uppercase text-gray-500">NK</dt>
                            <dd class="mt-2 fs-2 fw-bold text-gray-900">
                                {{ $akreditasi->nk !== null ? number_format($akreditasi->nk, 2) : '—' }}
                            </dd>
                            @if($akreditasi->is_nk_final)
                                <x-metronic.badge type="success" label="Final" class="mt-1" />
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded border border-gray-200 p-4 text-center">
                            <dt class="fs-8 fw-medium text-uppercase text-gray-500">NV</dt>
                            <dd class="mt-2 fs-2 fw-bold text-gray-900">
                                {{ $akreditasi->nv !== null ? number_format($akreditasi->nv, 2) : '—' }}
                            </dd>
                            @if($akreditasi->is_nv_final)
                                <x-metronic.badge type="success" label="Final" class="mt-1" />
                            @endif
                            @if($akreditasi->nv_override)
                                <x-metronic.badge type="warning" label="Override" class="mt-1" />
                            @endif
                        </div>
                    </div>
                </div>

                @if($akreditasi->nv_override && $akreditasi->nv_override_reason)
                    <div class="mt-4 rounded bg-light-warning border border-warning p-3">
                        <p class="fs-8 fw-medium text-warning">Alasan Override NV:</p>
                        <p class="mt-1 fs-7 text-warning">{{ $akreditasi->nv_override_reason }}</p>
                    </div>
                @endif
            </x-metronic.card>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4 d-grid gap-6">
            {{-- Peringkat & Nilai Akhir --}}
            <x-metronic.card class="overflow-hidden">
                <div class="bg-primary px-6 py-6 text-center text-white">
                    <p class="fs-8 fw-medium text-uppercase text-white opacity-75">Nilai Akhir</p>
                    <p class="mt-2 fs-1 fw-bold">
                        {{ $akreditasi->nilai !== null ? number_format($akreditasi->nilai, 2) : '—' }}
                    </p>
                </div>
                <div class="px-6 py-5 text-center">
                    <p class="fs-8 fw-medium text-uppercase text-gray-500">Peringkat</p>
                    @if($akreditasi->peringkat)
                        <div class="mt-2 d-inline-flex w-65px h-65px align-items-center justify-content-center rounded-circle bg-light-{{ $colorKey === 'green' ? 'success' : ($colorKey === 'blue' ? 'primary' : ($colorKey === 'yellow' ? 'warning' : 'danger')) }}">
                            <span class="fs-2 fw-bold {{ 'text-' . ($colorKey === 'green' ? 'success' : ($colorKey === 'blue' ? 'primary' : ($colorKey === 'yellow' ? 'warning' : 'danger'))) }}">
                                {{ $akreditasi->peringkat }}
                            </span>
                        </div>
                        <p class="mt-2 fs-7 fw-semibold text-gray-900">
                            {{ $peringkatLabels[$akreditasi->peringkat] ?? $akreditasi->peringkat }}
                        </p>
                    @else
                        <p class="mt-2 fs-7 text-gray-500">Belum tersedia</p>
                    @endif
                </div>
            </x-metronic.card>

            {{-- Sertifikat Download --}}
            @if($akreditasi->sertifikat_path)
                <x-metronic.card title="Sertifikat">
                    <a href="{{ asset('storage/' . $akreditasi->sertifikat_path) }}"
                       target="_blank"
                       class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="ki-outline ki-file-down fs-5"></i>
                        Unduh Sertifikat
                    </a>
                </x-metronic.card>
            @endif

            {{-- Visitasi Info --}}
            @if($akreditasi->tgl_visitasi)
                <x-metronic.card title="Visitasi">
                    <div class="d-grid gap-3">
                        <div>
                            <dt class="fs-8 fw-medium text-uppercase text-gray-500">Tanggal Mulai</dt>
                            <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->tgl_visitasi->format('d M Y, H:i') }}</dd>
                        </div>
                        @if($akreditasi->tgl_visitasi_akhir)
                            <div>
                                <dt class="fs-8 fw-medium text-uppercase text-gray-500">Tanggal Akhir</dt>
                                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->tgl_visitasi_akhir->format('d M Y, H:i') }}</dd>
                            </div>
                        @endif
                    </div>
                </x-metronic.card>
            @endif

            {{-- Appeal Action --}}
            @if($akreditasi->status === Akreditasi::STATUS_FINAL_REJECTED)
                <x-metronic.card>
                    <div class="text-center">
                        <p class="fs-7 text-muted">Tidak setuju dengan hasil?</p>
                        <a href="{{ route('pesantren.akreditasi.index') }}#banding-{{ $akreditasi->id }}"
                           class="mt-3 btn btn-outline btn-outline-secondary w-100">
                            Ajukan Banding
                        </a>
                    </div>
                </x-metronic.card>
            @endif
        </div>
    </div>

    {{-- Catatan --}}
    @if($akreditasi->catatan_rekomendasi_admin)
        <div class="mt-8">
            <x-metronic.card title="Catatan & Rekomendasi Admin">
                <p class="fs-7 text-gray-600 white-space-pre-line">{{ $akreditasi->catatan_rekomendasi_admin }}</p>
            </x-metronic.card>
        </div>
    @endif
</div>
@endsection
