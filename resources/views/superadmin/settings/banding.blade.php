@extends('layouts.metronic.app')

@section('title', 'Pengaturan Banding')
@section('pageTitle', 'Pengaturan Banding')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['key' => 'banding_eligibility', 'label' => 'Kriteria Kelayakan Banding', 'description' => 'Menentukan siapa yang dapat mengajukan banding atas hasil akreditasi.', 'type' => 'radio', 'options' => ['all' => 'Semua — Semua pesantren dengan hasil final ditolak dapat mengajukan banding', 'disabled' => 'Nonaktif — Pengajuan banding ditutup'], 'icon' => 'ki-message-question', 'color' => 'secondary', 'help' => 'Gunakan nonaktif bila periode banding ditutup sementara.'],
    ];
@endphp

<x-metronic.card title="Pengaturan Banding">
    <x-slot:header>
        <span class="badge badge-light-secondary">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light p-5 mb-6">
        <div class="fw-bold text-gray-800 mb-1">Kebijakan Banding</div>
        <div class="fs-7 text-muted">Atur kelayakan pengajuan banding agar proses pasca-hasil tetap jelas dan terdokumentasi.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
