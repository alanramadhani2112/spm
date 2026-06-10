@extends('layouts.metronic.app')

@section('title', 'Asesmen Akreditasi')
@section('pageTitle', 'Asesmen Akreditasi')

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

<div class="mx-auto">
    <div class="mb-8 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Pengisian Instrumen Asesmen</h2>
            <p class="mt-1 fs-7 text-muted">UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span></p>
        </div>
        <x-metronic.badge type="primary" :label="$akreditasi->getStatusLabel()" />
    </div>

    <x-metronic.card x-data="{ activeTab: 'ipm' }">
        {{-- Tab Navigation --}}
        <div class="border-bottom border-gray-200 mb-6">
            <nav class="d-flex table-responsive" aria-label="Tabs">
                <button type="button"
                        @click="activeTab = 'ipm'"
                        :class="activeTab === 'ipm' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                        class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-notepad fs-5 me-2"></i>
                    IPM
                </button>

                <button type="button"
                        @click="activeTab = 'edpm'"
                        :class="activeTab === 'edpm' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                        class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-chart-simple fs-5 me-2"></i>
                    EDPM / IPR
                </button>

                <button type="button"
                        @click="activeTab = 'sdm'"
                        :class="activeTab === 'sdm' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                        class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-profile-user fs-5 me-2"></i>
                    SDM
                </button>

                <button type="button"
                        @click="activeTab = 'dokumen'"
                        :class="activeTab === 'dokumen' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                        class="d-flex flex-shrink-0 border-bottom border-2 px-8 py-4 fs-7 fw-medium bg-transparent border-0">
                    <i class="ki-outline ki-file fs-5 me-2"></i>
                    Dokumen
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <form method="POST" action="{{ route('pesantren.akreditasi.submit-assessment', $akreditasi->id) }}" enctype="multipart/form-data">
            @csrf

            <div>
                {{-- IPM Tab --}}
                <div x-show="activeTab === 'ipm'" x-cloak>
                    <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Instrumen Penilaian Mutu (IPM)</h3>
                    <p class="fs-7 text-muted mb-6">Isi data instrument penilaian mutu pesantren sesuai dengan kondisi aktual.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center">
                        <i class="ki-outline ki-notepad fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir IPM akan tersedia setelah verifikasi data awal selesai.</p>
                        <p class="mt-1 fs-8 text-gray-500">Data Anda akan direview oleh admin sebelum instrumen dibuka.</p>
                    </div>
                </div>

                {{-- EDPM / IPR Tab --}}
                <div x-show="activeTab === 'edpm'" x-cloak>
                    <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Evaluasi Diri Pesantren (EDPM/IPR)</h3>
                    <p class="fs-7 text-muted mb-6">Isi data evaluasi diri pesantren berdasarkan kondisi nyata di lapangan.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center">
                        <i class="ki-outline ki-chart-simple fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir EDPM/IPR akan tersedia setelah verifikasi data awal selesai.</p>
                        <p class="mt-1 fs-8 text-gray-500">Isi semua butir evaluasi dengan data yang akurat dan dapat dipertanggungjawabkan.</p>
                    </div>
                </div>

                {{-- SDM Tab --}}
                <div x-show="activeTab === 'sdm'" x-cloak>
                    <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Sumber Daya Manusia (SDM)</h3>
                    <p class="fs-7 text-muted mb-6">Isi data tenaga pendidik, kependidikan, dan pengelola pesantren.</p>

                    <div class="rounded border border-dashed border-gray-300 bg-light p-6 text-center">
                        <i class="ki-outline ki-profile-user fs-2x text-gray-400"></i>
                        <p class="mt-3 fs-7 text-muted">Formulir data SDM akan tersedia setelah verifikasi data awal selesai.</p>
                        <p class="mt-1 fs-8 text-gray-500">Masukkan data guru, ustadz, pengasuh, dan tenaga kependidikan lainnya.</p>
                    </div>
                </div>

                {{-- Dokumen Tab --}}
                <div x-show="activeTab === 'dokumen'" x-cloak>
                    <h3 class="fs-5 fw-semibold text-gray-900 mb-2">Dokumen Pendukung</h3>
                    <p class="fs-7 text-muted mb-6">Unggah dokumen pendukung yang diperlukan untuk proses akreditasi.</p>

                    <div class="row g-4">
                        @php
                            $dokumenFields = [
                                'dok_profil' => 'Dokumen Profil Pesantren',
                                'dok_nsp' => 'Sertifikat NSP',
                                'dok_renstra' => 'Rencana Strategis',
                                'dok_rk_anggaran' => 'RK Anggaran',
                                'dok_kurikulum' => 'Dokumen Kurikulum',
                                'dok_silabus_rpp' => 'Silabus & RPP',
                                'dok_kepengasuhan' => 'Dokumen Kepengasuhan',
                                'dok_peraturan_kepegawaian' => 'Peraturan Kepegawaian',
                                'dok_sarpras' => 'Dokumen Sarpras',
                                'dok_laporan_tahunan' => 'Laporan Tahunan',
                                'dok_sop' => 'Dokumen SOP',
                            ];
                        @endphp

                        @foreach($dokumenFields as $field => $label)
                            <div class="col-md-6">
                                <x-metronic.form-input
                                    name="{{ $field }}"
                                    label="{{ $label }}"
                                    type="file"
                                    help="Format: PDF, max 5MB"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="ki-outline ki-sms fs-5"></i>
                        Simpan & Kirim
                    </button>
                </div>
            </x-slot:footer>
        </form>
    </x-metronic.card>
</div>
@endsection
