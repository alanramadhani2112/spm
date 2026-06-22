@extends('layouts.metronic.app')

@section('title', 'Koreksi Akreditasi')
@section('pageTitle', 'Koreksi Akreditasi')

@section('toolbar')
<a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
    $isStage1 = $akreditasi->status === Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION;
    $editorLabel = $isStage1 ? 'Admin' : 'Ketua Asesor';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-1">Koreksi Akreditasi</h2>
        <p class="fs-7 text-muted mb-0">{{ $editorLabel }} meminta perbaikan data. Silakan perbaiki, lalu klik Kirim Koreksi.</p>
    </div>
    <span class="badge badge-light-warning fs-7 px-3 py-2">{{ $akreditasi->getStatusLabel() }}</span>
</div>

<form method="POST" action="{{ route(''pesantren.akreditasi.submit-koreksi'', $akreditasi->id) }}" enctype="multipart/form-data">
    @csrf

    <x-metronic.card title="Profil Pesantren" flush>
        <div class="p-6">
            @include(''pesantren.data._profile-fields'', [''pesantren'' => $pesantren])
        </div>
    </x-metronic.card>

    <x-metronic.card title="IPM (4 Butir)" flush class="mt-6">
        <div class="p-6">
            @include(''pesantren.data._ipm-fields'', [''ipm'' => $ipm])
        </div>
    </x-metronic.card>

    <x-metronic.card title="SDM" flush class="mt-6">
        <div class="p-6">
            @include(''pesantren.data._sdm-fields'', [''sdm'' => $sdm])
        </div>
    </x-metronic.card>

    <x-metronic.card title="EDPM (40 Butir)" flush class="mt-6">
        <div class="p-6">
            @include(''pesantren.data._edpm-fields'', [''edpm'' => $edpm])
        </div>
    </x-metronic.card>

    <x-metronic.card title="Dokumen IPR (22 Butir)" flush class="mt-6">
        <div class="p-6">
            @include(''pesantren.data._ipr-fields'', [''ipr'' => $ipr])
        </div>
    </x-metronic.card>

    <x-metronic.card title="Dokumen" flush class="mt-6">
        <div class="p-6">
            @include(''pesantren.data._document-fields'', [''pesantren'' => $pesantren])
        </div>
    </x-metronic.card>

    <div class="d-flex justify-content-end gap-3 mt-6">
        <a href="{{ route(''pesantren.akreditasi.index'') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary" data-swal-confirm="true" data-swal-title="Kirim koreksi?" data-swal-text="Data yang sudah diperbaiki akan dikirim untuk ditinjau ulang." data-swal-icon="question" data-swal-confirm-button="Ya, kirim">
            <i class="ki-outline ki-send fs-4"></i>Kirim Koreksi
        </button>
    </div>
</form>
@endsection
