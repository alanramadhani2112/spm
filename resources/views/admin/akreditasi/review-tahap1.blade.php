@extends('layouts.metronic.app')

@section('title', 'Review Tahap 1')
@section('pageTitle', 'Review Tahap 1')

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Review Administratif Tahap 1</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
                &middot; Pesantren: <span>{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
            </p>
        </div>
        <x-metronic.badge type="warning" :label="$akreditasi->getStatusLabel()" pill />
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    @if($errors->any())
        <x-metronic.alert type="danger">
            <p class="fw-medium">Terjadi kesalahan validasi:</p>
            <ul class="mt-1 list-disc list-inside ms-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-metronic.alert>
    @endif

    <div x-data="{ activeTab: 'ipm', showCorrectionForm: false }" class="rounded-3 bg-white shadow-sm mb-6">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="d-flex -mb-px table-responsive" aria-label="Tabs">
                <button type="button"
                        @click="activeTab = 'ipm'"
                        :class="activeTab === 'ipm' ? 'border-blue-500 text-primary' : 'border-transparent text-muted hover:border-gray-300 hover:text-gray-700'"
                        class="flex-flex-shrink-0 border-b-2 px-6 py-4 fs-7 fw-medium">
                    IPM (Instrumen Penilaian Mutu)
                </button>

                <button type="button"
                        @click="activeTab = 'edpm'"
                        :class="activeTab === 'edpm' ? 'border-blue-500 text-primary' : 'border-transparent text-muted hover:border-gray-300 hover:text-gray-700'"
                        class="flex-flex-shrink-0 border-b-2 px-6 py-4 fs-7 fw-medium">
                    EDPM / IPR
                </button>

                <button type="button"
                        @click="activeTab = 'sdm'"
                        :class="activeTab === 'sdm' ? 'border-blue-500 text-primary' : 'border-transparent text-muted hover:border-gray-300 hover:text-gray-700'"
                        class="flex-flex-shrink-0 border-b-2 px-6 py-4 fs-7 fw-medium">
                    SDM
                </button>

                <button type="button"
                        @click="activeTab = 'berkas'"
                        :class="activeTab === 'berkas' ? 'border-blue-500 text-primary' : 'border-transparent text-muted hover:border-gray-300 hover:text-gray-700'"
                        class="flex-flex-shrink-0 border-b-2 px-6 py-4 fs-7 fw-medium">
                    Dokumen
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="px-6 py-6">
            {{-- IPM Tab --}}
            <div x-show="activeTab === 'ipm'" x-cloak>
                <div class="rounded border border-gray-100 bg-light p-6 text-center">
                    <svg class="mx-auto w-40px h-40px text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <h3 class="mt-3 fs-7 fw-semibold text-gray-700">Data IPM</h3>
                    <p class="mt-1 fs-7 text-muted">Instrumen Penilaian Mutu yang diisi oleh pesantren.</p>
                    <p class="mt-3 fs-8 text-gray-500">Review detail IPM tersedia setelah assessment dibuka oleh pesantren.</p>
                </div>
            </div>

            {{-- EDPM Tab --}}
            <div x-show="activeTab === 'edpm'" x-cloak>
                <div class="rounded border border-gray-100 bg-light p-6 text-center">
                    <svg class="mx-auto w-40px h-40px text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                    </svg>
                    <h3 class="mt-3 fs-7 fw-semibold text-gray-700">EDPM / IPR</h3>
                    <p class="mt-1 fs-7 text-muted">Evaluasi Diri Pesantren &mdash; mutu internal pesantren.</p>
                    <p class="mt-3 fs-8 text-gray-500">Review detail EDPM tersedia setelah assessment dibuka oleh pesantren.</p>
                </div>
            </div>

            {{-- SDM Tab --}}
            <div x-show="activeTab === 'sdm'" x-cloak>
                <div class="rounded border border-gray-100 bg-light p-6 text-center">
                    <svg class="mx-auto w-40px h-40px text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <h3 class="mt-3 fs-7 fw-semibold text-gray-700">Data SDM</h3>
                    <p class="mt-1 fs-7 text-muted">Sumber Daya Manusia &mdash; guru, ustadz, santri, tenaga kependidikan.</p>
                    <p class="mt-3 fs-8 text-gray-500">Review detail SDM tersedia setelah assessment dibuka oleh pesantren.</p>
                </div>
            </div>

            {{-- Dokumen Tab --}}
            <div x-show="activeTab === 'berkas'" x-cloak>
                <div class="rounded border border-gray-100 bg-light p-6 text-center">
                    <svg class="mx-auto w-40px h-40px text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <h3 class="mt-3 fs-7 fw-semibold text-gray-700">Berkas Pendukung</h3>
                    <p class="mt-1 fs-7 text-muted">Dokumen yang diunggah oleh pesantren sebagai bukti pendukung.</p>
                    <p class="mt-3 fs-8 text-gray-500">Dokumen tersedia setelah pesantren mengunggah berkas pendukung.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Aksi Review --}}
    <div class="row gap-6">
        {{-- Approve --}}
        <div class="rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-success">Setujui Tahap 1</h2>
                <p class="mt-1 fs-7 text-muted">Data telah lengkap dan valid. Lanjutkan ke penugasan asesor.</p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.approve-tahap1', $akreditasi->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="catatan_approve" class="block fs-7 fw-medium text-gray-700">
                            Catatan (opsional)
                        </label>
                        <textarea id="catatan_approve" name="catatan" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-green-500 focus:"
                                  placeholder="Catatan persetujuan...">{{ old('catatan') }}</textarea>
                    </div>
                    <button type="submit"
                            class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-success px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                        <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Setujui Tahap 1
                    </button>
                </form>
            </div>
        </div>

        {{-- Koreksi --}}
        <div class="rounded-3 bg-white shadow-sm" x-data="{ sections: [] }">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-warning">Minta Perbaikan</h2>
                <p class="mt-1 fs-7 text-muted">Beberapa bagian perlu diperbaiki oleh pesantren.</p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.minta-perbaikan-tahap1', $akreditasi->id) }}">
                    @csrf
                    <fieldset class="mb-4">
                        <legend class="fs-7 fw-medium text-gray-700 mb-3">Bagian yang perlu dikoreksi <span class="text-danger">*</span></legend>
                        <div class="d-grid gap-2.5">
                            <label class="d-flex align-items-center gap-3 rounded border border-gray-200 px-4 py-3 cursor-pointer">
                                <input type="checkbox" name="sections[]" value="ipm"
                                       class="w-15px h-15px rounded border-gray-300 text-orange-600"
                                       x-model="sections"
                                       {{ in_array('ipm', old('sections', [])) ? 'checked' : '' }}>
                                <span class="fs-7">IPM (Instrumen Penilaian Mutu)</span>
                            </label>
                            <label class="d-flex align-items-center gap-3 rounded border border-gray-200 px-4 py-3 cursor-pointer">
                                <input type="checkbox" name="sections[]" value="edpm"
                                       class="w-15px h-15px rounded border-gray-300 text-orange-600"
                                       x-model="sections"
                                       {{ in_array('edpm', old('sections', [])) ? 'checked' : '' }}>
                                <span class="fs-7">EDPM / IPR</span>
                            </label>
                            <label class="d-flex align-items-center gap-3 rounded border border-gray-200 px-4 py-3 cursor-pointer">
                                <input type="checkbox" name="sections[]" value="sdm"
                                       class="w-15px h-15px rounded border-gray-300 text-orange-600"
                                       x-model="sections"
                                       {{ in_array('sdm', old('sections', [])) ? 'checked' : '' }}>
                                <span class="fs-7">SDM (Sumber Daya Manusia)</span>
                            </label>
                        </div>
                        <p x-show="sections.length === 0" class="mt-2 fs-8 text-danger">Pilih minimal satu bagian.</p>
                    </fieldset>

                    <div class="mb-4">
                        <label for="reason_correction" class="block fs-7 fw-medium text-gray-700">
                            Alasan Perbaikan
                        </label>
                        <textarea id="reason_correction" name="reason" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-orange-500 focus:"
                                  placeholder="Jelaskan apa yang perlu diperbaiki...">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit"
                            class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded bg-orange-600 px-4 py-2 fs-7 fw-semibold text-white shadow-sm -orange-600">
                        <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                        </svg>
                        Minta Perbaikan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Limit Review Section (only for limit_review status) --}}
    @if($akreditasi->status === Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW)
        <div class="mt-6 rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-warning">Peninjauan Batas Koreksi</h2>
                <p class="mt-1 fs-7 text-muted">
                    Siklus koreksi: {{ $akreditasi->correction_cycle }}. Pesantren telah mencapai batas koreksi tahap 1.
                </p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.handle-limit-review', $akreditasi->id) }}">
                    @csrf
                    <div class="row gap-6">
                        <div class="rounded border border-green-100 bg-light-success p-4">
                            <label class="d-flex items-start gap-3 cursor-pointer">
                                <input type="radio" name="action" value="approve_by_exception"
                                       class="mt-1 w-15px h-15px border-gray-300 text-success"
                                       {{ old('action') === 'approve_by_exception' ? 'checked' : '' }}>
                                <div>
                                    <p class="fs-7 fw-medium text-green-800">Setujui dengan Pengecualian</p>
                                    <p class="mt-1 fs-8 text-success">Lanjutkan ke tahap penugasan asesor meskipun telah mencapai batas koreksi.</p>
                                </div>
                            </label>
                        </div>
                        <div class="rounded border border-red-100 bg-light-danger p-4">
                            <label class="d-flex items-start gap-3 cursor-pointer">
                                <input type="radio" name="action" value="reject_administrative"
                                       class="mt-1 w-15px h-15px border-gray-300 text-danger"
                                       x-on:change="document.getElementById('limit_reason').required = true"
                                       {{ old('action') === 'reject_administrative' ? 'checked' : '' }}>
                                <div>
                                    <p class="fs-7 fw-medium text-red-800">Tolak Administratif</p>
                                    <p class="mt-1 fs-8 text-danger">Hentikan proses akreditasi karena tidak memenuhi standar.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="limit_reason" class="block fs-7 fw-medium text-gray-700">
                            Alasan / Catatan
                        </label>
                        <textarea id="limit_reason" name="reason" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-yellow-500 focus:"
                                  placeholder="Alasan atau catatan untuk keputusan ini...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="mt-5 flex justify-content-end">
                        <button type="submit"
                                class="d-inline-flex align-items-center gap-2 rounded bg-yellow-600 px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                            Proses Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    </div>

@endsection
