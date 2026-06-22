@extends('layouts.metronic.app')

@section('title', ($isSuperAdminView ?? false) ? 'Input NK — Super Admin' : 'Input NK — Ketua Asesor')
@section('pageTitle', ($isSuperAdminView ?? false) ? 'Input Nilai Komponen (NK) — Super Admin' : 'Input Nilai Komponen (NK)')

@section('content')
@php
    use App\Services\ScoringService;
    $inputRouteName = $inputRouteName ?? 'asesor.ketua.input-nk';
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

    @if($errors->any())
        <x-metronic.alert type="danger">
            <p class="fw-semibold mb-2">Mohon periksa kembali:</p>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-metronic.alert>
    @endif

    <div class="row g-5 g-xl-8">
        <div class="col-md-4">
            <div class="card card-flush bg-light p-5 text-center">
                <p class="fs-8 fw-medium text-muted mb-2">Nilai NA1 (Ketua)</p>
                <p class="fs-2 fw-bold text-gray-900 mb-2">{{ $akreditasi->na1 !== null ? number_format($akreditasi->na1, 2) : '—' }}</p>
                <span class="badge badge-light-success">
                    <i class="ki-outline ki-check-squared fs-8 me-1"></i> Final
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush bg-light p-5 text-center">
                <p class="fs-8 fw-medium text-muted mb-2">Nilai NA2 (Anggota)</p>
                <p class="fs-2 fw-bold text-gray-900 mb-2">{{ $akreditasi->na2 !== null ? number_format($akreditasi->na2, 2) : '—' }}</p>
                <span class="badge badge-light-success">
                    <i class="ki-outline ki-check-squared fs-8 me-1"></i> Final
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush bg-light p-5 text-center">
                <p class="fs-8 fw-medium text-muted mb-2">Nilai NK</p>
                <p class="fs-2 fw-bold text-gray-900 mb-2">{{ $akreditasi->nk !== null ? number_format($akreditasi->nk, 2) : '—' }}</p>
                <span class="badge {{ $akreditasi->is_nk_final ? 'badge-light-success' : 'badge-light-secondary' }}">
                    {{ $akreditasi->is_nk_final ? 'Final' : 'Belum Final' }}
                </span>
            </div>
        </div>
    </div>

    <x-metronic.card title="Input Nilai Komponen (NK)">
        <x-slot:header>
            <div class="d-flex align-items-center justify-content-between w-100">
                <span class="text-muted fs-7">Akreditasi: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 12, '...') }}</span>
                @if($akreditasi->is_nk_final)
                    <span class="badge badge-light-success">
                        <i class="ki-outline ki-check-squared fs-8 me-1"></i>
                        NK Final
                    </span>
                @endif
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route($inputRouteName, $akreditasi->id) }}" data-swal-confirm="true" data-swal-title="Simpan nilai NK?" data-swal-text="Nilai NK untuk pengajuan {{ $akreditasi->uuid }} akan disimpan. Jika finalisasi dicentang, nilai tidak dapat diubah kembali." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
            @csrf

            @foreach($komponen as $k)
                <div class="{{ !$loop->first ? 'mt-8 pt-8 border-top' : '' }}">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="badge badge-light-primary fs-8 fw-bold">{{ $k->id }}</span>
                        <h3 class="fs-6 fw-bold text-gray-900 mb-0">{{ $k->nama ?? 'Komponen ' . $k->id }}</h3>
                    </div>

                    @if($k->butirs->isNotEmpty())
                        <div class="d-grid gap-3">
                            @foreach($k->butirs as $butir)
                                <div class="border border-gray-300 rounded p-4">
                                    @php
                                        $na1 = $na1Scores[$butir->id] ?? null;
                                        $na2 = $na2Scores[$butir->id] ?? null;
                                        $hasBothScores = $na1 !== null && $na2 !== null;
                                        $delta = $hasBothScores ? $na1 - $na2 : null;
                                        $hasDelta = $delta !== null && $delta !== 0;
                                        $defaultNk = $hasBothScores ? (($hasDelta ? (old("butir.{$butir->id}") ?? $nkScores[$butir->id] ?? null) : $na1)) : null;
                                    @endphp
                                    <div class="d-flex align-items-start gap-4">
                                        <div class="flex-grow-1 min-w-0">
                                            <p class="fs-7 fw-semibold text-gray-900 mb-1">
                                                @if($butir->kode ?? false)
                                                    <span class="badge badge-light-primary fs-8 me-1">{{ $butir->kode }}</span>
                                                @endif
                                                {{ $butir->nama ?? 'Butir ' . $butir->id }}
                                            </p>
                                            @if($butir->deskripsi ?? false)
                                                <p class="fs-8 text-muted mb-3">{{ $butir->deskripsi }}</p>
                                            @endif

                                            <div class="d-flex gap-4 mb-2">
                                                <div class="border rounded p-2 text-center" style="min-width: 60px;">
                                                    <div class="fs-8 text-muted mb-1">NA1</div>
                                                    <div class="fs-6 fw-bold text-gray-800">{{ $na1 !== null ? $na1 : '-' }}</div>
                                                </div>
                                                <div class="border rounded p-2 text-center" style="min-width: 60px;">
                                                    <div class="fs-8 text-muted mb-1">NA2</div>
                                                    <div class="fs-6 fw-bold text-gray-800">{{ $na2 !== null ? $na2 : '-' }}</div>
                                                </div>
                                                <div class="border rounded p-2 text-center {{ $hasDelta ? 'bg-light-warning border-warning' : 'bg-light-success border-success' }}" style="min-width: 60px;">
                                                    <div class="fs-8 text-muted mb-1">Delta</div>
                                                    <div class="fs-6 fw-bold {{ $hasDelta ? 'text-warning' : 'text-success' }}">{{ $delta }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-end flex-shrink-0 bg-light p-4 rounded">
                                            <div class="fs-8 fw-bold text-gray-700 mb-3">Input NK</div>
                                            <div class="d-flex align-items-center gap-3">
                                                @foreach($skala as $nilai)
                                                    <label class="d-flex align-items-center gap-1 {{ !$hasDelta && $nilai != $defaultNk ? 'opacity-50' : 'cursor-pointer' }}">
                                                        <input type="radio" name="butir[{{ $butir->id }}]" value="{{ $nilai }}"
                                                               class="form-check-input"
                                                               @checked($nilai == $defaultNk)
                                                               @disabled($akreditasi->is_nk_final || !$hasDelta)>
                                                        <span class="fs-6 fw-medium text-gray-800">{{ $nilai }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @if(!$hasDelta)
                                                <input type="hidden" name="butir[{{ $butir->id }}]" value="{{ $na1 }}">
                                                <div class="fs-9 text-muted mt-2"><i class="ki-outline ki-information fs-9 me-1"></i>Otomatis (Delta = 0)</div>
                                            @else
                                                <div class="fs-9 text-warning mt-2"><i class="ki-outline ki-warning-2 fs-9 me-1"></i>Perlu Input Manual</div>
                                            @endif
                                        </div>
                                    </div>
                                    @error("butir.{$butir->id}")
                                        <p class="mt-2 fs-8 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="fs-7 text-muted fst-italic">Belum ada butir penilaian untuk komponen ini.</p>
                    @endif
                </div>
            @endforeach

            @unless($akreditasi->is_nk_final)
            <div class="mt-8 border border-primary bg-light-primary rounded p-4">
                <label class="d-flex align-items-start gap-3">
                    <input type="checkbox" name="set_final" value="1"
                           class="form-check-input mt-1"
                           @checked(old('set_final'))>
                    <div>
                        <p class="fs-7 fw-semibold text-gray-900 mb-0">Finalisasi Nilai NK</p>
                        <p class="fs-8 text-primary mb-0">Setelah difinalisasi, nilai NK tidak dapat diubah kembali.</p>
                    </div>
                </label>
            </div>

            <div class="mt-6 d-flex align-items-center gap-4">
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="ki-outline ki-file-down fs-5 me-2"></i>
                    Simpan Nilai NK
                </button>

                <a href="{{ route($backRouteName) }}" class="btn btn-light">Kembali</a>
            </div>
            @else
            <div class="mt-6">
                <a href="{{ route($backRouteName) }}" class="btn btn-light">
                    <i class="ki-outline ki-arrow-left fs-5 me-2"></i>
                    Kembali ke Dashboard
                </a>
            </div>
            @endunless
        </form>
    </x-metronic.card>
</div>
@endsection



