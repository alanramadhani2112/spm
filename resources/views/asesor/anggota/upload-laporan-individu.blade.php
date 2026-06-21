@extends('layouts.metronic.app')

@section('title', ($isSuperAdminView ?? false) ? 'Upload Laporan Individu — Super Admin' : 'Upload Laporan Individu — Anggota Asesor')
@section('pageTitle', ($isSuperAdminView ?? false) ? 'Upload Laporan Individu — Super Admin' : 'Upload Laporan Individu')

@section('content')
@php
    $uploadRouteName = $uploadRouteName ?? 'asesor.anggota.upload-laporan-individu';
    $backRouteName = $backRouteName ?? 'asesor.anggota.index';
@endphp

    @includeWhen($isSuperAdminView ?? false, 'superadmin._mode-banner')

<div class="d-grid gap-6">
    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    <x-metronic.card title="Upload Laporan Individu">
        <x-slot:header>
            <span class="text-muted fs-7">Akreditasi: {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 12, '...') }}</span>
        </x-slot:header>

        <form method="POST" action="{{ route($uploadRouteName, $akreditasi->id) }}" enctype="multipart/form-data" data-swal-confirm="true" data-swal-title="Upload laporan?" data-swal-text="Laporan individu akan diunggah untuk akreditasi ini." data-swal-icon="question" data-swal-confirm-button="Ya, upload">
            @csrf
            <div class="row g-5">
                <div class="col-12">
                    <x-metronic.form-input name="laporan" label="File Laporan Individu" type="file" :required="true" help="Format PDF, DOC, atau DOCX. Maksimal 10MB." />
                </div>
            </div>
            <div class="d-flex justify-content-end gap-3 mt-6">
                <a href="{{ route($backRouteName) }}" class="btn btn-light">Kembali</a>
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-cloud-add fs-4"></i>Upload</button>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
