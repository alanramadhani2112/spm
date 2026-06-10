@extends('layouts.metronic.app')

@section('title', 'Pengajuan Akreditasi Baru')
@section('pageTitle', 'Pengajuan Akreditasi Baru')

@section('content')
@php
    $checks = [
        'profilMinimum' => ['label' => 'Profil pesantren telah diisi lengkap', 'ok' => $completeness['profilMinimum'] ?? false],
        'assessmentReady' => ['label' => 'Unit pendidikan, IPM, SDM, dan EDPM telah lengkap', 'ok' => $completeness['assessmentReady'] ?? false],
        'locked' => ['label' => 'Profil belum terkunci oleh proses aktif', 'ok' => !($completeness['locked'] ?? false)],
    ];
@endphp

<div class="mx-auto">
    <x-metronic.card title="Konfirmasi Pengajuan">
        <p class="fs-7 text-muted mb-6">Pastikan data pesantren Anda telah lengkap sebelum mengajukan akreditasi.</p>

        <div class="rounded border {{ ($completeness['assessmentReady'] ?? false) ? 'border-success bg-light-success' : 'border-warning bg-light-warning' }} p-4 mb-6">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline {{ ($completeness['assessmentReady'] ?? false) ? 'ki-check-circle' : 'ki-information-3' }} fs-2 {{ ($completeness['assessmentReady'] ?? false) ? 'text-success' : 'text-warning' }} mt-1"></i>
                <div class="fs-7 w-100">
                    <p class="fw-medium {{ ($completeness['assessmentReady'] ?? false) ? 'text-success' : 'text-warning' }}">Status Kelengkapan Data</p>
                    <ul class="mt-2 list-unstyled mb-0">
                        @foreach($checks as $check)
                            <li class="mb-2 d-flex align-items-center gap-2 {{ $check['ok'] ? 'text-success' : 'text-warning' }}">
                                <i class="ki-outline {{ $check['ok'] ? 'ki-check' : 'ki-cross' }} fs-5"></i>
                                {{ $check['label'] }}
                            </li>
                        @endforeach
                    </ul>
                    @if(!($completeness['assessmentReady'] ?? false))
                        <div class="mt-3">
                            <a href="{{ route('pesantren.data.index') }}" class="btn btn-sm btn-warning">Lengkapi Data</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded border border-warning bg-light-warning p-4 mb-6">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-3 fs-2 text-warning mt-1"></i>
                <div class="fs-7">
                    <p class="fw-medium text-warning">Perhatian:</p>
                    <p class="mt-1 text-warning">Setelah pengajuan dikirim, data profil akan dikunci dan tidak dapat diubah selama proses akreditasi berlangsung.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('pesantren.akreditasi.submit-pengajuan') }}">
            @csrf

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" @disabled(!($completeness['assessmentReady'] ?? false))>
                    <i class="ki-outline ki-sms fs-5"></i>
                    Kirim Pengajuan
                </button>
                <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">Batal</a>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
