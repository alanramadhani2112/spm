@extends('layouts.metronic.app')

@section('title', 'Kelengkapan Data Pesantren')
@section('pageTitle', 'Kelengkapan Data Pesantren')

@section('content')
@php
    $checks = [
        'profilMinimum' => ['label' => 'Profil minimum', 'ok' => $completeness['profilMinimum'] ?? false],
        'unit' => ['label' => 'Unit pendidikan', 'ok' => $pesantren?->units?->isNotEmpty() ?? false],
        'ipm' => ['label' => 'Data IPM', 'ok' => (bool) $ipm],
        'sdm' => ['label' => 'Data SDM', 'ok' => (bool) $sdm],
        'edpm' => ['label' => 'Data EDPM/IPR', 'ok' => (bool) $edpm],
    ];
@endphp

<div class="row g-5 g-xl-8 mb-8">
    @foreach($checks as $check)
        <div class="col-xl col-md-4">
            <div class="card card-flush h-100 {{ $check['ok'] ? 'bg-light-success' : 'bg-light-warning' }}">
                <div class="card-body d-flex align-items-center gap-4 p-5">
                    <span class="symbol symbol-40px">
                        <span class="symbol-label {{ $check['ok'] ? 'bg-success' : 'bg-warning' }} text-white">
                            <i class="ki-outline {{ $check['ok'] ? 'ki-check' : 'ki-information-3' }} fs-2 text-white"></i>
                        </span>
                    </span>
                    <div>
                        <div class="fw-bold text-gray-900 fs-7">{{ $check['label'] }}</div>
                        <div class="fs-8 text-muted">{{ $check['ok'] ? 'Lengkap' : 'Belum lengkap' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($completeness['assessmentReady'] ?? false)
    <x-metronic.alert type="success" message="Data prasyarat sudah lengkap. Anda dapat mengajukan atau melanjutkan proses akreditasi." />
@else
    <x-metronic.alert type="warning">
        <div class="fw-semibold mb-1">Data belum lengkap.</div>
        <div>Lengkapi seluruh bagian sebelum mengajukan akreditasi. Field wajib yang belum lengkap: {{ implode(', ', $completeness['missingFields'] ?? []) ?: 'unit/IPM/SDM/EDPM' }}.</div>
    </x-metronic.alert>
@endif

<div x-data="{ activeTab: 'profile' }" class="card card-flush">
    <div class="card-header pt-5">
        <div class="card-title"><h3 class="fw-bold text-gray-900">Form Kelengkapan Data</h3></div>
    </div>
    <div class="card-body">
        <div class="border-bottom border-gray-200 mb-8">
            <nav class="d-flex table-responsive" aria-label="Tabs">
                <button type="button" @click="activeTab = 'profile'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">Profil & Unit</button>
                <button type="button" @click="activeTab = 'ipm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">IPM</button>
                <button type="button" @click="activeTab = 'sdm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">SDM</button>
                <button type="button" @click="activeTab = 'edpm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">EDPM/IPR</button>
            </nav>
        </div>

        <div x-show="activeTab === 'profile'" x-cloak>
            <form method="POST" action="{{ route('pesantren.data.profile') }}" enctype="multipart/form-data">
                @csrf
                @if($pesantren?->is_locked)
                    <x-metronic.alert type="info" message="Profil pesantren sudah dikunci karena pengajuan telah dikirim." />
                @endif
                @include('pesantren.data._profile-fields', ['pesantren' => $pesantren])
                <div class="d-flex justify-content-end gap-3">
                    <button type="submit" class="btn btn-primary" @disabled($pesantren?->is_locked)>Simpan Profil & Unit</button>
                </div>
            </form>
        </div>

        <div x-show="activeTab === 'ipm'" x-cloak>
            <form method="POST" action="{{ route('pesantren.data.ipm') }}">
                @csrf
                @include('pesantren.data._ipm-fields', ['ipm' => $ipm])
                <div class="d-flex justify-content-end gap-3 mt-6"><button type="submit" class="btn btn-primary">Simpan IPM</button></div>
            </form>
        </div>

        <div x-show="activeTab === 'sdm'" x-cloak>
            <form method="POST" action="{{ route('pesantren.data.sdm') }}">
                @csrf
                @include('pesantren.data._sdm-fields', ['sdm' => $sdm])
                <div class="d-flex justify-content-end gap-3 mt-6"><button type="submit" class="btn btn-primary">Simpan SDM</button></div>
            </form>
        </div>

        <div x-show="activeTab === 'edpm'" x-cloak>
            <form method="POST" action="{{ route('pesantren.data.edpm') }}">
                @csrf
                @include('pesantren.data._edpm-fields', ['edpm' => $edpm])
                <div class="d-flex justify-content-end gap-3 mt-6"><button type="submit" class="btn btn-primary">Simpan EDPM/IPR</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
