@extends('layouts.metronic.app')

@section('title', 'Pengaturan Deadline')
@section('pageTitle', 'Pengaturan Deadline')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['key' => 'review_awal_deadline', 'label' => 'Deadline Review Awal', 'description' => 'Batas waktu tim admin melakukan review awal pengajuan.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-search-list', 'color' => 'primary', 'help' => 'Disarankan cukup singkat agar pengajuan baru cepat diproses.'],
        ['key' => 'assessment_deadline', 'label' => 'Deadline Asesmen', 'description' => 'Batas waktu pesantren melengkapi instrumen assessment.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-document', 'color' => 'info', 'help' => 'Beri ruang cukup untuk pengisian IPM, SDM, dan EDPM/IPR.'],
        ['key' => 'review_tahap1_deadline', 'label' => 'Deadline Review Tahap 1', 'description' => 'Batas waktu review administratif tahap 1.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-check-square', 'color' => 'warning'],
        ['key' => 'correction_tahap1_deadline', 'label' => 'Deadline Koreksi Tahap 1', 'description' => 'Batas waktu pesantren memperbaiki data tahap 1.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-arrows-circle', 'color' => 'warning'],
        ['key' => 'review_tahap2_deadline', 'label' => 'Deadline Review Tahap 2', 'description' => 'Batas waktu asesor melakukan review dokumen tahap 2.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-profile-user', 'color' => 'success'],
        ['key' => 'correction_tahap2_deadline', 'label' => 'Deadline Koreksi Tahap 2', 'description' => 'Batas waktu pesantren memperbaiki dokumen tahap 2.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-notepad-edit', 'color' => 'success'],
        ['key' => 'scoring_deadline', 'label' => 'Deadline Penilaian', 'description' => 'Batas waktu pengisian nilai setelah visitasi.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-chart-line', 'color' => 'danger'],
        ['key' => 'banding_deadline', 'label' => 'Deadline Banding', 'description' => 'Batas waktu pesantren mengajukan banding setelah hasil diterbitkan.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-message-question', 'color' => 'secondary'],
    ];
@endphp

<x-metronic.card title="Pengaturan Deadline">
    <x-slot:header>
        <span class="badge badge-light-primary">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light-primary p-5 mb-6">
        <div class="fw-bold text-primary mb-1">SLA Workflow Akreditasi</div>
        <div class="fs-7 text-muted">Atur batas hari setiap tahapan. Setiap perubahan wajib memiliki alasan dan akan tercatat di audit log.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
