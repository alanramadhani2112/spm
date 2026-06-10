@extends('layouts.metronic.app')

@section('title', 'Upload Laporan — Ketua Asesor')
@section('pageTitle', 'Upload Laporan Visitasi')

@section('content')
@php
    use App\Models\Akreditasi;
    $uploadRouteName = $uploadRouteName ?? 'asesor.ketua.upload-laporan';
    $backRouteName = $backRouteName ?? 'asesor.ketua.index';
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

    <x-metronic.card title="Upload Laporan Visitasi">
        <p class="text-muted fs-7">Akreditasi: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 12, '...') }}</p>

        <form method="POST" action="{{ route($uploadRouteName, $akreditasi->id) }}" enctype="multipart/form-data" class="mt-6" data-swal-confirm="true" data-swal-title="Upload laporan visitasi?" data-swal-text="Laporan visitasi untuk pengajuan {{ $akreditasi->uuid }} akan diunggah." data-swal-icon="question" data-swal-confirm-button="Ya, upload" data-swal-confirm-class="btn btn-primary">
            @csrf

            <div class="d-grid gap-6">
                <div>
                    <label for="laporan_individu" class="form-label required">Laporan Individu</label>
                    <div class="border border-dashed border-gray-400 rounded p-10 text-center">
                        <i class="ki-outline ki-file-up fs-2x text-muted mb-4"></i>
                        <div class="fs-7 text-gray-600 mb-2">
                            <label for="laporan_individu" class="fw-bold text-primary cursor-pointer">
                                <span>Pilih file</span>
                                <input type="file" name="laporan_individu" id="laporan_individu" accept=".pdf,.doc,.docx" required class="d-none" />
                            </label>
                            <span class="ms-1">atau seret ke sini</span>
                        </div>
                        <p class="fs-8 text-muted mb-0">PDF, DOC, DOCX hingga 10MB</p>
                    </div>
                    @error('laporan_individu')
                        <p class="mt-2 fs-8 text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="laporan_kelompok" class="form-label required">Laporan Kelompok</label>
                    <div class="border border-dashed border-gray-400 rounded p-10 text-center">
                        <i class="ki-outline ki-file-up fs-2x text-muted mb-4"></i>
                        <div class="fs-7 text-gray-600 mb-2">
                            <label for="laporan_kelompok" class="fw-bold text-primary cursor-pointer">
                                <span>Pilih file</span>
                                <input type="file" name="laporan_kelompok" id="laporan_kelompok" accept=".pdf,.doc,.docx" required class="d-none" />
                            </label>
                            <span class="ms-1">atau seret ke sini</span>
                        </div>
                        <p class="fs-8 text-muted mb-0">PDF, DOC, DOCX hingga 10MB</p>
                    </div>
                    @error('laporan_kelompok')
                        <p class="mt-2 fs-8 text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 d-flex align-items-center justify-content-end gap-3">
                <a href="{{ route($backRouteName) }}" class="btn btn-light">Kembali</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="ki-outline ki-file-up fs-5 me-2"></i>
                    Upload Laporan
                </button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
