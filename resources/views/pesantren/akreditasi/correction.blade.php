@extends('layouts.metronic.app')

@section('title', 'Koreksi Data')
@section('pageTitle', 'Koreksi Data')

@section('content')
@php
    use App\Models\Akreditasi;

    $latestRejection = $akreditasi->rejections()
        ->where('akreditasi_id', $akreditasi->id)
        ->latest()
        ->first();

    $correctionSections = $latestRejection ? ($latestRejection->sections ?? []) : [];
    $correctionReason = $latestRejection ? $latestRejection->reason : null;
    $isStage1 = $akreditasi->status === Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION;
    $isStage2 = $akreditasi->status === Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION;

    $submitRoute = $isStage1
        ? route('pesantren.akreditasi.submit-koreksi', $akreditasi->id)
        : route('pesantren.akreditasi.submit-koreksi', $akreditasi->id);
@endphp

<div class="mx-auto">
    <div class="mb-8 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Koreksi Data Akreditasi</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
                &middot; Siklus: {{ $akreditasi->correction_cycle }}
            </p>
        </div>
        <x-metronic.badge type="warning" :label="$akreditasi->getStatusLabel()" />
    </div>

    @if($correctionReason)
        <div class="rounded border border-warning bg-light-warning p-4 mb-8">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-3 fs-2 text-warning mt-1"></i>
                <div>
                    <p class="fs-7 fw-medium text-warning">Catatan Koreksi:</p>
                    <p class="mt-1 fs-7 text-warning">{{ $correctionReason }}</p>
                    @if(!empty($correctionSections))
                        <p class="mt-2 fs-8 fw-medium text-warning">
                            Bagian yang perlu dikoreksi:
                            <span class="fw-semibold">
                                {{ implode(', ', array_map(fn($s) => strtoupper($s), $correctionSections)) }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
        @csrf

        <div class="d-grid gap-6">
            {{-- IPM Correction Section --}}
            @if(in_array('ipm', $correctionSections))
                <x-metronic.card title="Koreksi Data IPM">
                    <p class="fs-7 text-muted">Perbaiki data Instrumen Penilaian Mutu sesuai catatan.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center mt-4">
                        <i class="ki-outline ki-notepad fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir koreksi IPM akan tersedia setelah data instrumen diisi.</p>
                    </div>
                </x-metronic.card>
            @endif

            {{-- SDM Correction Section --}}
            @if(in_array('sdm', $correctionSections))
                <x-metronic.card title="Koreksi Data SDM">
                    <p class="fs-7 text-muted">Perbaiki data Sumber Daya Manusia sesuai catatan.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center mt-4">
                        <i class="ki-outline ki-profile-user fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir koreksi SDM akan tersedia setelah data diisi.</p>
                    </div>
                </x-metronic.card>
            @endif

            {{-- EDPM Correction Section --}}
            @if(in_array('edpm', $correctionSections))
                <x-metronic.card title="Koreksi Data EDPM/IPR">
                    <p class="fs-7 text-muted">Perbaiki data Evaluasi Diri Pesantren sesuai catatan.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center mt-4">
                        <i class="ki-outline ki-chart-simple fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir koreksi EDPM akan tersedia setelah data diisi.</p>
                    </div>
                </x-metronic.card>
            @endif

            @if(empty($correctionSections))
                <x-metronic.card>
                    <div class="text-center py-10">
                        <i class="ki-outline ki-verify fs-2x text-gray-300"></i>
                        <p class="mt-3 fs-7 text-muted">Tidak ada bagian yang memerlukan koreksi.</p>
                        <p class="mt-1 fs-8 text-gray-500">Silakan tunggu informasi lebih lanjut dari admin.</p>
                    </div>
                </x-metronic.card>
            @endif
        </div>

        @if(!empty($correctionSections))
            <div class="mt-6 d-flex align-items-center justify-content-end gap-3">
                <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">
                    Kembali
                </a>
                <button type="submit" class="btn btn-warning d-inline-flex align-items-center gap-2">
                    <i class="ki-outline ki-sms fs-5"></i>
                    Kirim Koreksi
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
