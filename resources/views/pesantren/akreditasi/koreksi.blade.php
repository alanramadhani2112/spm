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

<form method="POST" action="{{ route('pesantren.akreditasi.submit-koreksi', $akreditasi->id) }}" enctype="multipart/form-data">
    @csrf

    <x-metronic.card title="Data Pesantren" flush>
        <div class="p-6">
            <div class="row g-5">
                <div class="col-md-6">
                    <x-metronic.form-input name="nama_pesantren" label="Nama Pesantren" :value="$pesantren?->nama_pesantren" />
                    <x-metronic.form-input name="ns_pesantren" label="NS Pesantren" :value="$pesantren?->ns_pesantren" />
                    <x-metronic.form-input name="alamat" label="Alamat" type="textarea" :value="$pesantren?->alamat" :rows="2" />
                    <x-metronic.form-input name="telp_pesantren" label="Telepon" :value="$pesantren?->telp_pesantren" />
                    <x-metronic.form-input name="email_pesantren" label="Email" type="email" :value="$pesantren?->email_pesantren" />
                </div>
                <div class="col-md-6">
                    <x-metronic.form-input name="hp_wa" label="HP/WA" :value="$pesantren?->hp_wa" />
                    <x-metronic.form-input name="nama_mudir" label="Nama Mudir" :value="$pesantren?->nama_mudir" />
                    <x-metronic.form-input name="visi" label="Visi" type="textarea" :value="$pesantren?->visi" :rows="2" />
                    <x-metronic.form-input name="misi" label="Misi" type="textarea" :value="$pesantren?->misi" :rows="2" />
                </div>
            </div>
        </div>
    </x-metronic.card>

    <x-metronic.card title="IPM" flush class="mt-6">
        <div class="p-6">
            <div class="row g-5">
                <div class="col-md-6">
                    <x-metronic.form-input name="ipm[santri_mukim]" label="Santri Mukim" type="number" :value="$ipm?->data['santri_mukim'] ?? ''" />
                    <x-metronic.form-input name="ipm[santri_non_mukim]" label="Santri Non-Mukim" type="number" :value="$ipm?->data['santri_non_mukim'] ?? ''" />
                </div>
                <div class="col-md-6">
                    <x-metronic.form-input name="ipm[jumlah_rombongan_belajar]" label="Jumlah Rombel" type="number" :value="$ipm?->data['jumlah_rombongan_belajar'] ?? ''" />
                    <x-metronic.form-input name="ipm[kurikulum_utama]" label="Kurikulum Utama" :value="$ipm?->data['kurikulum_utama'] ?? ''" />
                </div>
            </div>
        </div>
    </x-metronic.card>

    <x-metronic.card title="SDM" flush class="mt-6">
        <div class="p-6">
            <div class="row g-5">
                <div class="col-md-6">
                    <x-metronic.form-input name="sdm[ustaz_tetap]" label="Ustaz Tetap" type="number" :value="$sdm?->data['ustaz_tetap'] ?? ''" />
                    <x-metronic.form-input name="sdm[ustaz_tidak_tetap]" label="Ustaz Tidak Tetap" type="number" :value="$sdm?->data['ustaz_tidak_tetap'] ?? ''" />
                </div>
                <div class="col-md-6">
                    <x-metronic.form-input name="sdm[tenaga_kependidikan]" label="Tenaga Kependidikan" type="number" :value="$sdm?->data['tenaga_kependidikan'] ?? ''" />
                    <x-metronic.form-input name="sdm[rasio_pengasuh_santri]" label="Rasio Pengasuh" :value="$sdm?->data['rasio_pengasuh_santri'] ?? ''" />
                </div>
            </div>
        </div>
    </x-metronic.card>

    <x-metronic.card title="EDPM" flush class="mt-6">
        <div class="p-6">
            <x-metronic.form-input name="edpm[self_assessment]" label="Self Assessment" type="textarea" :value="$edpm?->data['self_assessment'] ?? ''" :rows="4" />
        </div>
    </x-metronic.card>

    <div class="d-flex justify-content-end gap-3 mt-6">
        <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary" data-swal-confirm="true" data-swal-title="Kirim koreksi?" data-swal-text="Data yang sudah diperbaiki akan dikirim untuk ditinjau ulang." data-swal-icon="question" data-swal-confirm-button="Ya, kirim">
            <i class="ki-outline ki-send fs-4"></i>Kirim Koreksi
        </button>
    </div>
</form>
@endsection
