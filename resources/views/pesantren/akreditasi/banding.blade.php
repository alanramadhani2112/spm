@extends('layouts.metronic.app')

@section('title', 'Ajukan Banding')
@section('pageTitle', 'Ajukan Banding')

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

<div class="mx-auto">
    <div class="mb-8 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Pengajuan Banding</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
            </p>
        </div>
        <x-metronic.badge type="danger" :label="$akreditasi->getStatusLabel()" />
    </div>

    <x-metronic.card title="Formulir Banding">
        <p class="fs-7 text-muted mb-6">Sampaikan alasan Anda mengajukan banding atas hasil akreditasi.</p>

        <div class="rounded border border-warning bg-light-warning p-4 mb-8">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-3 fs-2 text-warning mt-1"></i>
                <div class="fs-7">
                    <p class="fw-medium text-warning">Perhatian:</p>
                    <ul class="mt-1 list-unstyled">
                        <li class="mb-1 text-warning">Banding hanya dapat diajukan satu kali</li>
                        <li class="mb-1 text-warning">Sertakan alasan yang jelas dan berdasar</li>
                        <li class="text-warning">Proses banding akan direview oleh tim asesor</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('pesantren.akreditasi.submit-banding', $akreditasi->id) }}">
            @csrf

            <x-metronic.form-input
                name="alasan"
                label="Alasan Banding"
                type="textarea"
                :value="old('alasan')"
                :required="true"
                placeholder="Tuliskan alasan pengajuan banding Anda di sini..."
                :rows="8"
                help="Jelaskan secara rinci mengapa Anda mengajukan banding."
            />

            <div class="d-flex align-items-center justify-content-end gap-3">
                <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">
                    Kembali
                </a>
                <button type="submit" class="btn btn-warning d-inline-flex align-items-center gap-2">
                    <i class="ki-outline ki-information-3 fs-5"></i>
                    Ajukan Banding
                </button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
