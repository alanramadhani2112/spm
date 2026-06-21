@extends('layouts.metronic.app')

@section('title', ($isSuperAdminView ?? false) ? 'Input IPR — Super Admin' : 'Input IPR — Ketua Asesor')
@section('pageTitle', ($isSuperAdminView ?? false) ? 'Input IPR — Super Admin' : 'Input Instrumen Penilaian Readiness (IPR)')

@section('content')
@php
    use App\Services\ScoringService;
    $inputRouteName = $inputRouteName ?? 'asesor.ketua.input-ipr';
    $backRouteName = $backRouteName ?? 'asesor.ketua.index';
    $skala = ScoringService::SKALA_NILAI;
@endphp

    @includeWhen($isSuperAdminView ?? false, 'superadmin._mode-banner')

<div class="d-grid gap-6">
    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    <x-metronic.card title="Skoring IPR (22 Butir)">
        <x-slot:header>
            <div class="d-flex align-items-center justify-content-between w-100">
                <span class="text-muted fs-7">Akreditasi: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 12, '...') }}</span>
                @if($akreditasi->is_nv_final)
                    <span class="badge badge-light-success"><i class="ki-outline ki-check-squared fs-8 me-1"></i>IPR Final</span>
                @endif
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route($inputRouteName, $akreditasi->id) }}" data-swal-confirm="true" data-swal-title="Simpan nilai IPR?" data-swal-text="Nilai IPR akan disimpan. Jika finalisasi dicentang, nilai tidak dapat diubah kembali." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-danger">
            @csrf

            <div class="mb-6 p-4 rounded bg-light-info">
                <div class="d-flex align-items-start gap-3">
                    <i class="ki-outline ki-information-2 fs-4 text-info mt-1"></i>
                    <div class="fs-7 text-gray-700">
                        <strong>IPR (Instrumen Penilaian Readiness)</strong> menilai kesiapan pesantren berdasarkan 22 butir. Skala penilaian: 1 (Kurang) — 4 (Sangat Baik).
                    </div>
                </div>
            </div>

            @foreach($iprButirs as $butir)
                <div class="mb-6 pb-6 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="badge badge-light-primary fs-8 fw-bold">{{ $butir->kode ?? $butir->id }}</span>
                        <div>
                            <div class="fw-semibold text-gray-900">{{ $butir->nama ?? $butir->name }}</div>
                            @if($butir->deskripsi)
                                <div class="fs-8 text-muted mt-1">{{ $butir->deskripsi }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        @foreach($skala as $nilai)
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="butir[{{ $butir->id }}]" value="{{ $nilai }}" @checked(old('butir.'.$butir->id, $existingScores[$butir->id] ?? null) == $nilai)>
                                <span class="form-check-label">{{ $nilai }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="separator separator-dashed my-6"></div>

            <div class="form-check form-switch mb-6">
                <input class="form-check-input" type="checkbox" name="set_final" value="1" id="set_final">
                <label class="form-check-label fw-semibold text-gray-900" for="set_final">Finalisasi — nilai tidak dapat diubah kembali setelah final</label>
            </div>

            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route($backRouteName) }}" class="btn btn-light">Kembali</a>
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-check-squared fs-4"></i>Simpan IPR</button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
