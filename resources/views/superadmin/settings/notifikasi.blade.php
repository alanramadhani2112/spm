@extends('layouts.metronic.app')

@section('title', 'Pengaturan Notifikasi')
@section('pageTitle', 'Pengaturan Notifikasi')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['key' => 'superadmin_receives_admin_notif', 'label' => 'Super Admin Menerima Notifikasi Admin', 'description' => 'Super Admin akan menerima salinan semua notifikasi yang dikirim ke admin.', 'type' => 'radio', 'options' => ['1' => 'Ya', '0' => 'Tidak'], 'icon' => 'ki-notification', 'color' => 'danger', 'help' => 'Aktifkan agar Super Admin selalu mendapat visibilitas atas notifikasi operasional.'],
        ['key' => 'reminder_days', 'label' => 'Jumlah Hari Pengingat', 'description' => 'Notifikasi pengingat dikirim H-berapa sebelum deadline tercapai.', 'type' => 'number', 'unit' => 'hari', 'icon' => 'ki-calendar-tick', 'color' => 'warning', 'help' => 'Gunakan angka kecil untuk reminder yang lebih dekat dengan deadline.'],
    ];
@endphp

<x-metronic.card title="Pengaturan Notifikasi">
    <x-slot:header>
        <span class="badge badge-light-danger">{{ count($settingCards) }} parameter</span>
    </x-slot:header>

    <div class="rounded bg-light-danger p-5 mb-6">
        <div class="fw-bold text-danger mb-1">Visibilitas dan Reminder</div>
        <div class="fs-7 text-muted">Atur bagaimana Super Admin menerima salinan notifikasi dan kapan pengingat deadline dikirim.</div>
    </div>

    <div class="d-grid gap-5">
        @foreach($settingCards as $setting)
            @include('superadmin.settings._setting-card', ['setting' => $setting, 'settings' => $settings])
        @endforeach
    </div>
</x-metronic.card>
@endsection
