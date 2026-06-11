@extends('layouts.metronic.app')

@section('title', 'Pengaturan Koreksi')
@section('pageTitle', 'Pengaturan Koreksi')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['key' => 'max_siklus_tahap1', 'label' => 'Maksimal Siklus Koreksi Tahap 1', 'description' => 'Jumlah maksimal putaran koreksi administratif tahap 1.', 'type' => 'number', 'unit' => 'siklus', 'icon' => 'ki-arrows-circle', 'color' => 'warning', 'help' => 'Saat batas tercapai, pengajuan masuk ke review batas koreksi.'],
        ['key' => 'max_siklus_tahap2', 'label' => 'Maksimal Siklus Koreksi Tahap 2', 'description' => 'Jumlah maksimal putaran koreksi dokumen tahap 2.', 'type' => 'number', 'unit' => 'siklus', 'icon' => 'ki-arrows-circle', 'color' => 'success', 'help' => 'Dipakai untuk membatasi koreksi setelah review asesor.'],
        ['key' => 'action_on_limit', 'label' => 'Tindakan Saat Batas Siklus Tercapai', 'description' => 'Default keputusan ketika pengajuan mencapai batas koreksi.', 'type' => 'select', 'options' => ['reject' => 'Tolak Pengajuan', 'auto_approve' => 'Setujui Otomatis', 'freeze' => 'Bekukan'], 'icon' => 'ki-shield-tick', 'color' => 'danger', 'help' => 'Pilih tindakan default yang paling sesuai dengan kebijakan operasional.'],
    ];
@endphp

<x-metronic.card title="Pengaturan Koreksi">
    <x-slot:header>
        <span class="badge badge-light-warning">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light-warning p-5 mb-6">
        <div class="fw-bold text-warning mb-1">Kontrol Siklus Perbaikan</div>
        <div class="fs-7 text-muted">Batasi jumlah koreksi agar workflow tetap terkendali dan keputusan batas koreksi terdokumentasi.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
