@extends('layouts.metronic.app')

@section('title', 'Terbitkan SK')
@section('pageTitle', 'Terbitkan SK Akreditasi')

@section('content')
@php
    use App\Models\Akreditasi;
    $akreditasiRoutePrefix = $akreditasiRoutePrefix ?? 'admin.akreditasi';
@endphp
    @includeWhen($isSuperAdminView ?? false, 'superadmin._mode-banner')

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Terbitkan SK Akreditasi</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
                &middot; Pesantren: <span>{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
            </p>
        </div>
        <x-metronic.badge type="success" :label="$akreditasi->getStatusLabel()" pill />
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

    {{-- Result Summary --}}
    <div class="row gap-4 mb-6">
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Nilai Akhir</p>
            <p class="mt-2 fs-2 fw-semibold text-gray-900">{{ number_format($akreditasi->nilai ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Peringkat</p>
            <p class="mt-2 fs-2 fw-semibold text-gray-900">{{ $akreditasi->peringkat ?? '—' }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NV (Final)</p>
            <p class="mt-2 fs-2 fw-semibold text-success">{{ number_format($akreditasi->nv ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- SK Form --}}
    <div class="rounded-3 bg-white shadow-sm">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Form Penerbitan SK</h2>
            <p class="mt-1 fs-7 text-muted">Tentukan nomor SK dan masa berlaku akreditasi.</p>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route($akreditasiRoutePrefix.'.terbitkan-sk', $akreditasi->id) }}" data-swal-confirm="true" data-swal-title="Terbitkan SK akreditasi?" data-swal-text="SK untuk pengajuan {{ $akreditasi->uuid }} akan diterbitkan dan status menjadi selesai." data-swal-icon="warning" data-swal-confirm-button="Ya, terbitkan" data-swal-confirm-class="btn btn-success">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="nomor_sk" class="block fs-7 fw-medium text-gray-700">
                            Nomor SK <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nomor_sk" name="nomor_sk"
                               value="{{ old('nomor_sk') }}"
                               maxlength="100"
                               required
                               class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-green-500 focus:"
                               placeholder="Contoh: SK/1234/AKREDITASI/VI/2025" />
                        <p class="mt-1 fs-8 text-gray-500">Maksimal 100 karakter.</p>
                    </div>

                    <div>
                        <label for="masa_berlaku" class="block fs-7 fw-medium text-gray-700">
                            Masa Berlaku SK <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="masa_berlaku" name="masa_berlaku"
                               value="{{ old('masa_berlaku') }}"
                               required
                               class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 focus:border-green-500 focus:" />
                        <p class="mt-1 fs-8 text-gray-500">Tanggal mulai berlakunya SK akreditasi.</p>
                    </div>

                    <div class="rounded border border-blue-100 bg-light-primary p-4">
                        <div class="d-flex align-items-start gap-3">
                            <i class="ki-outline ki-information-4 fs-2 text-primary mt-1"></i>
                            <div class="fs-7">
                                <p class="fw-medium">Setelah SK diterbitkan:</p>
                                <ul class="mt-1 list-disc list-inside text-primary space-y-0.5">
                                    <li>Akreditasi akan berstatus Selesai (Completed)</li>
                                    <li>Sertifikat SK akan digenerate secara otomatis</li>
                                    <li>Pesantren akan dapat mengunduh sertifikat</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                        <a href="{{ route($akreditasiRoutePrefix.'.index') }}"
                           class="rounded border border-gray-200 bg-white px-4 py-2 fs-7 fw-medium text-gray-600 shadow-sm">
                            Batal
                        </a>
                        <button type="submit"
                                class="d-inline-flex align-items-center gap-2 rounded btn btn-success px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                            <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Terbitkan SK
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection
