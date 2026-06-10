@extends('layouts.metronic.app')

@section('title', 'Asesmen Akreditasi')
@section('pageTitle', 'Asesmen Akreditasi')

@section('content')
<div class="mx-auto">
    <div class="mb-8 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Pengisian Instrumen Asesmen</h2>
            <p class="mt-1 fs-7 text-muted">UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span></p>
        </div>
        <x-metronic.badge type="primary" :label="$akreditasi->getStatusLabel()" />
    </div>

    <x-metronic.card x-data="{ activeTab: 'ipm' }">
        <div class="border-bottom border-gray-200 mb-6">
            <nav class="d-flex table-responsive" aria-label="Tabs">
                <button type="button" @click="activeTab = 'ipm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-notepad fs-5 me-2"></i>IPM
                </button>
                <button type="button" @click="activeTab = 'edpm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-chart-simple fs-5 me-2"></i>EDPM / IPR
                </button>
                <button type="button" @click="activeTab = 'sdm'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-profile-user fs-5 me-2"></i>SDM
                </button>
                <button type="button" @click="activeTab = 'dokumen'" class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-file fs-5 me-2"></i>Dokumen
                </button>
            </nav>
        </div>

        <form method="POST" action="{{ route('pesantren.akreditasi.submit-assessment', $akreditasi->id) }}" enctype="multipart/form-data">
            @csrf

            <div x-show="activeTab === 'ipm'" x-cloak>
                <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Instrumen Penilaian Mutu (IPM)</h3>
                <p class="fs-7 text-muted mb-6">Perbarui data mutu pesantren sebelum mengirim asesmen.</p>
                @include('pesantren.data._ipm-fields', ['ipm' => $ipm])
            </div>

            <div x-show="activeTab === 'edpm'" x-cloak>
                <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Evaluasi Diri Pesantren (EDPM/IPR)</h3>
                <p class="fs-7 text-muted mb-6">Isi evaluasi diri berdasarkan kondisi nyata di lapangan.</p>
                @include('pesantren.data._edpm-fields', ['edpm' => $edpm])
            </div>

            <div x-show="activeTab === 'sdm'" x-cloak>
                <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Sumber Daya Manusia (SDM)</h3>
                <p class="fs-7 text-muted mb-6">Isi data tenaga pendidik, kependidikan, dan pengelola pesantren.</p>
                @include('pesantren.data._sdm-fields', ['sdm' => $sdm])
            </div>

            <div x-show="activeTab === 'dokumen'" x-cloak>
                <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Dokumen Pendukung</h3>
                <p class="fs-7 text-muted mb-6">Unggah dokumen pendukung yang diperlukan untuk proses akreditasi.</p>
                @include('pesantren.data._document-fields', ['pesantren' => $pesantren])
            </div>

            <x-slot:footer>
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">Kembali</a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="ki-outline ki-sms fs-5"></i>Simpan & Kirim
                    </button>
                </div>
            </x-slot:footer>
        </form>
    </x-metronic.card>
</div>
@endsection
