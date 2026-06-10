@extends('layouts.metronic.app')

@section('title', 'Kartu Kendali')
@section('pageTitle', 'Kartu Kendali')

@section('content')
<div class="mx-auto">
    <div class="mb-8">
        <h2 class="fs-5 fw-semibold text-gray-900">Unggah Kartu Kendali</h2>
        <p class="mt-1 fs-7 text-muted">Unggah dokumen kartu kendali yang telah ditandatangani setelah visitasi.</p>
    </div>

    <x-metronic.card title="Upload Dokumen">
        <div class="rounded border border-primary bg-light-primary p-4 mb-8">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-5 fs-2 text-primary mt-1"></i>
                <div class="fs-7">
                    <p class="fw-medium text-primary">Petunjuk:</p>
                    <ul class="mt-1 list-unstyled">
                        <li class="mb-1 text-primary">Pastikan kartu kendali telah ditandatangani oleh asesor</li>
                        <li class="mb-1 text-primary">Format file: PDF</li>
                        <li class="mb-1 text-primary">Ukuran maksimal: 10MB</li>
                        <li class="text-primary">Upload hanya dapat dilakukan satu kali</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('pesantren.akreditasi.upload-kk', $akreditasi->id) }}" enctype="multipart/form-data">
            @csrf

            <x-metronic.form-input
                name="file"
                label="File Kartu Kendali"
                type="file"
                :required="true"
                help="PDF hingga 10MB"
            />

            <div class="d-flex align-items-center justify-content-end gap-3">
                <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">
                    Kembali
                </a>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="ki-outline ki-file-up fs-5"></i>
                    Upload
                </button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
