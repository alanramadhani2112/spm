@extends('layouts.metronic.app')

@section('title', 'Pengaturan Dokumen')
@section('pageTitle', 'Pengaturan Dokumen')

@section('content')
@include('superadmin.settings._nav')

@php
    $phaseOptions = [
        'before_admin_validation' => 'Sebelum Validasi Admin',
        'before_submit' => 'Sebelum Submit',
        'before_visitasi' => 'Sebelum Visitasi',
    ];
    $settingCards = [
        ['key' => 'kartu_kendali_wajib_before', 'label' => 'Kartu Kendali Wajib Diunggah Sebelum', 'description' => 'Menentukan fase minimum sebelum kartu kendali wajib tersedia.', 'type' => 'radio', 'options' => $phaseOptions, 'icon' => 'ki-file-up', 'color' => 'info', 'help' => 'Gunakan untuk memastikan dokumen kontrol tersedia sebelum tahapan kritis.'],
        ['key' => 'laporan_wajib_before', 'label' => 'Laporan Wajib Diunggah Sebelum', 'description' => 'Menentukan fase minimum sebelum laporan visitasi wajib tersedia.', 'type' => 'radio', 'options' => $phaseOptions, 'icon' => 'ki-document', 'color' => 'primary', 'help' => 'Atur agar validasi tidak berjalan tanpa laporan yang diperlukan.'],
    ];
@endphp

<x-metronic.card title="Pengaturan Dokumen">
    <x-slot:header>
        <span class="badge badge-light-info">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light-info p-5 mb-6">
        <div class="fw-bold text-info mb-1">Gate Dokumen Workflow</div>
        <div class="fs-7 text-muted">Tetapkan kapan dokumen wajib harus tersedia agar alur validasi dan visitasi tetap tertib.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
