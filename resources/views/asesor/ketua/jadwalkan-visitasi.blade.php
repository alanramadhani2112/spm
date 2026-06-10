@extends('layouts.metronic.app')

@section('title', 'Jadwalkan Visitasi — Ketua Asesor')
@section('pageTitle', 'Jadwalkan Visitasi')

@section('content')
<div class="d-grid gap-6">

    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    <x-metronic.card title="Informasi Akreditasi">
        <dl class="row gap-4">
            <div>
                <dt class="fs-8 fw-medium text-muted">UUID</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->uuid }}</dd>
            </div>
            <div>
                <dt class="fs-8 fw-medium text-muted">Status</dt>
                <dd class="mt-1">
                    <span class="badge badge-light-info">{{ $akreditasi->getStatusLabel() }}</span>
                </dd>
            </div>
            <div>
                <dt class="fs-8 fw-medium text-muted">Nomor SK</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->nomor_sk ?? '—' }}</dd>
            </div>
            @if($akreditasi->tgl_visitasi)
            <div>
                <dt class="fs-8 fw-medium text-muted">Jadwal Saat Ini</dt>
                <dd class="mt-1 fs-7 text-gray-900">
                    {{ $akreditasi->tgl_visitasi->format('d M Y') }}
                    @if($akreditasi->tgl_visitasi_akhir)
                        — {{ $akreditasi->tgl_visitasi_akhir->format('d M Y') }}
                    @endif
                </dd>
            </div>
            @endif
        </dl>
    </x-metronic.card>

    <x-metronic.card title="{{ $akreditasi->tgl_visitasi ? 'Perbarui' : 'Tetapkan' }} Jadwal Visitasi">
        <p class="text-muted fs-7">Tentukan rentang tanggal pelaksanaan visitasi untuk akreditasi ini.</p>

        <form method="POST" action="{{ route('asesor.ketua.jadwalkan-visitasi', $akreditasi->id) }}" class="mt-6 d-grid gap-5">
            @csrf

            <div class="row g-5">
                <div class="col-md-6">
                    <x-metronic.form-input name="tgl_mulai" type="date" label="Tanggal Mulai" :value="old('tgl_mulai', $akreditasi->tgl_visitasi?->format('Y-m-d'))" required />
                </div>

                <div class="col-md-6">
                    <x-metronic.form-input name="tgl_akhir" type="date" label="Tanggal Akhir" :value="old('tgl_akhir', $akreditasi->tgl_visitasi_akhir?->format('Y-m-d'))" required />
                </div>
            </div>

            <x-metronic.form-input name="catatan" type="textarea" label="Catatan Visitasi" :value="old('catatan', $akreditasi->catatan_visitasi)" placeholder="Informasi tambahan seperti lokasi, ketentuan, dll." />

            <div class="d-flex align-items-center gap-4 pt-2">
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="ki-outline ki-calendar-8 fs-5 me-2"></i>
                    {{ $akreditasi->tgl_visitasi ? 'Perbarui Jadwal' : 'Tetapkan Jadwal' }}
                </button>

                <a href="{{ route('asesor.ketua.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
