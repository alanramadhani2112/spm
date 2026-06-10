@extends('layouts.metronic.app')

@section('title', 'Pengaturan Super Admin')
@section('pageTitle', 'Pengaturan Super Admin')

@section('content')
@include('superadmin.settings._nav')

@php
    $settingCards = [
        ['route' => 'superadmin.settings.deadline', 'title' => 'Deadline', 'desc' => 'Atur batas waktu review awal, assessment, koreksi, scoring, dan banding.', 'icon' => 'ki-calendar-tick', 'color' => 'primary', 'count' => count($categories['deadline'] ?? [])],
        ['route' => 'superadmin.settings.correction', 'title' => 'Koreksi', 'desc' => 'Kelola maksimal siklus koreksi dan tindakan saat batas koreksi tercapai.', 'icon' => 'ki-arrows-circle', 'color' => 'warning', 'count' => count($categories['correction'] ?? [])],
        ['route' => 'superadmin.settings.dokumen', 'title' => 'Dokumen', 'desc' => 'Atur kewajiban upload dokumen sebelum fase workflow tertentu.', 'icon' => 'ki-document', 'color' => 'info', 'count' => count($categories['document'] ?? [])],
        ['route' => 'superadmin.settings.nv', 'title' => 'Nilai Visitasi', 'desc' => 'Atur izin override NV dan mekanisme alasan perubahan nilai.', 'icon' => 'ki-chart-line', 'color' => 'success', 'count' => count($categories['nv'] ?? [])],
        ['route' => 'superadmin.settings.notifikasi', 'title' => 'Notifikasi', 'desc' => 'Atur penerimaan notifikasi Super Admin dan hari pengingat deadline.', 'icon' => 'ki-notification', 'color' => 'danger', 'count' => count($categories['notification'] ?? [])],
        ['route' => 'superadmin.settings.banding', 'title' => 'Banding', 'desc' => 'Atur kriteria kelayakan pengajuan banding pesantren.', 'icon' => 'ki-message-question', 'color' => 'secondary', 'count' => count($categories['banding'] ?? [])],
    ];
    $totalSettings = collect($settingCards)->sum('count');
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-setting-2 fs-2 text-white"></i></span>
                </div>
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Control Center Pengaturan</h2>
                    <div class="fs-7 text-muted">Kelola parameter workflow yang memengaruhi deadline, koreksi, dokumen, nilai, notifikasi, dan banding.</div>
                </div>
            </div>
            <span class="badge badge-light-primary">{{ $totalSettings }} parameter</span>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8">
    @foreach($settingCards as $card)
        <div class="col-xl-4 col-md-6">
            <a href="{{ route($card['route']) }}" class="card card-flush h-100 hover-elevate-up text-decoration-none border border-gray-200">
                <div class="card-body p-7">
                    <div class="d-flex align-items-center justify-content-between mb-6">
                        <span class="symbol symbol-45px">
                            <span class="symbol-label bg-light-{{ $card['color'] }}"><i class="ki-outline {{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i></span>
                        </span>
                        <span class="badge badge-light-{{ $card['color'] }}">{{ $card['count'] }} setting</span>
                    </div>
                    <h3 class="fs-5 fw-bold text-gray-900 mb-2">{{ $card['title'] }}</h3>
                    <p class="fs-7 text-muted mb-6">{{ $card['desc'] }}</p>
                    <span class="btn btn-sm btn-light-{{ $card['color'] }}">Kelola {{ $card['title'] }}</span>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
