@extends('layouts.metronic.app')

@section('title', 'Pengaturan Nilai Visitasi (NV)')
@section('pageTitle', 'Pengaturan Nilai Visitasi (NV)')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['key' => 'nv_override_allowed', 'label' => 'Izinkan Override NV', 'description' => 'Mengizinkan admin menimpa nilai visitasi yang dihitung otomatis.', 'type' => 'radio', 'options' => ['1' => 'Ya', '0' => 'Tidak'], 'icon' => 'ki-switch', 'color' => 'success', 'help' => 'Matikan jika nilai visitasi harus selalu mengikuti perhitungan sistem.'],
        ['key' => 'nv_reason_mode', 'label' => 'Mode Alasan Override NV', 'description' => 'Mengatur apakah alasan override dikumpulkan kolektif atau per butir.', 'type' => 'radio', 'options' => ['collective' => 'Kolektif — Satu alasan untuk seluruh NV', 'per_butir' => 'Per Butir — Alasan untuk setiap butir penilaian'], 'icon' => 'ki-notepad-edit', 'color' => 'primary', 'help' => 'Mode per butir memberi audit lebih detail, tetapi membutuhkan input lebih banyak.'],
    ];
@endphp

<x-metronic.card title="Pengaturan Nilai Visitasi (NV)">
    <x-slot:header>
        <span class="badge badge-light-success">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light-success p-5 mb-6">
        <div class="fw-bold text-success mb-1">Kontrol Nilai Visitasi</div>
        <div class="fs-7 text-muted">Atur fleksibilitas override nilai visitasi dan kualitas alasan perubahan nilai.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
