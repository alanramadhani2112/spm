@extends('layouts.metronic.app')

@section('title', 'Input NA2 — Anggota Asesor')
@section('pageTitle', 'Input Nilai Akreditasi 2 (NA2)')

@section('content')
@php
    use App\Services\ScoringService;
    $skala = ScoringService::SKALA_NILAI;
@endphp

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

    <x-metronic.card title="Skoring NA2">
        <x-slot:header>
            <div class="d-flex align-items-center justify-content-between w-100">
                <div>
                    <span class="text-muted fs-7">Akreditasi: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 12, '...') }}</span>
                </div>
                @if($akreditasi->is_na2_final)
                    <span class="badge badge-light-success">
                        <i class="ki-outline ki-check-squared fs-8 me-1"></i>
                        NA2 Final
                    </span>
                @endif
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route('asesor.anggota.input-na2', $akreditasi->id) }}" id="na2-form">
            @csrf

            @foreach($komponen as $k)
                <div class="{{ !$loop->first ? 'mt-8 pt-8 border-top' : '' }}">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="badge badge-light-danger fs-8 fw-bold">{{ $k->id }}</span>
                        <h3 class="fs-6 fw-bold text-gray-900 mb-0">{{ $k->nama ?? 'Komponen ' . $k->id }}</h3>
                        @if(isset(ScoringService::KOMPONEN_CONFIG[$k->kode ?? '']) || $k->id <= 4)
                            @php
                                $cfg = collect(ScoringService::KOMPONEN_CONFIG)->firstWhere('id', $k->id);
                            @endphp
                            @if($cfg)
                                <span class="fs-8 text-muted">Bobot: {{ $cfg['bobot'] }}%</span>
                            @endif
                        @endif
                    </div>

                    @if($k->butirs->isNotEmpty())
                        <div class="d-grid gap-3">
                            @foreach($k->butirs as $butir)
                                <div class="border border-gray-300 rounded p-4">
                                    <div class="d-flex align-items-start justify-content-between gap-4">
                                        <div class="flex-grow-1 min-w-0">
                                            <p class="fs-7 fw-semibold text-gray-900 mb-1">
                                                @if($butir->kode ?? false)
                                                    <span class="fs-8 text-muted">{{ $butir->kode }}</span>
                                                @endif
                                                {{ $butir->nama ?? 'Butir ' . $butir->id }}
                                            </p>
                                            @if($butir->deskripsi ?? false)
                                                <p class="fs-8 text-muted mb-0">{{ $butir->deskripsi }}</p>
                                            @endif
                                        </div>
                                        <div class="flex-shrink-0">
                                            <select name="butir[{{ $butir->id }}]"
                                                    class="form-select form-select-solid">
                                                <option value="">Pilih Nilai</option>
                                                @foreach($skala as $nilai)
                                                    <option value="{{ $nilai }}"
                                                            @selected(old("butir.{$butir->id}") == $nilai)>
                                                        {{ $nilai }}
                                                    </option>
                                                @endforeach
                                            </select>
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

            <div class="mt-8 border border-danger bg-light-danger rounded p-4">
                <label class="d-flex align-items-start gap-3">
                    <input type="checkbox" name="is_final" value="1"
                           class="form-check-input mt-1"
                           @checked(old('is_final'))>
                    <div>
                        <p class="fs-7 fw-semibold text-gray-900 mb-0">Finalisasi Nilai NA2</p>
                        <p class="fs-8 text-danger mb-0">Setelah difinalisasi, nilai NA2 tidak dapat diubah kembali.</p>
                    </div>
                </label>
            </div>

            <div class="mt-6 d-flex align-items-center justify-content-between">
                <a href="{{ route('asesor.anggota.index') }}" class="btn btn-light">Kembali</a>
                <button type="submit" class="btn btn-danger fw-bold">
                    <i class="ki-outline ki-check-squared fs-5 me-2"></i>
                    Simpan NA2
                </button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
