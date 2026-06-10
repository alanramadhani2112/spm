@extends('layouts.metronic.app')

@section('title', 'Pengaturan Super Admin')
@section('pageTitle', 'Pengaturan Super Admin')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Ringkasan Pengaturan">
    <p class="fs-6 text-gray-700 mb-6">Pilih kategori pengaturan dari menu navigasi di atas untuk mengelola konfigurasi sistem.</p>

    <div class="row g-5">
        <a href="{{ route('superadmin.settings.deadline') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Deadline</p>
            <p class="mt-1 fs-7 text-muted">Atur batas waktu setiap tahapan akreditasi.</p>
        </a>
        <a href="{{ route('superadmin.settings.correction') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Koreksi</p>
            <p class="mt-1 fs-7 text-muted">Atur maksimal siklus koreksi dan tindakan batas.</p>
        </a>
        <a href="{{ route('superadmin.settings.dokumen') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Dokumen</p>
            <p class="mt-1 fs-7 text-muted">Atur kewajiban unggah dokumen sebelum tahapan tertentu.</p>
        </a>
        <a href="{{ route('superadmin.settings.nv') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Nilai Visitasi</p>
            <p class="mt-1 fs-7 text-muted">Atur izin override NV dan mode alasan.</p>
        </a>
        <a href="{{ route('superadmin.settings.notifikasi') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Notifikasi</p>
            <p class="mt-1 fs-7 text-muted">Atur penerimaan notifikasi dan pengingat.</p>
        </a>
        <a href="{{ route('superadmin.settings.banding') }}"
           class="col-md-4 rounded border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
            <p class="fs-6 fw-bold text-gray-900">Banding</p>
            <p class="mt-1 fs-7 text-muted">Atur kriteria kelayakan pengajuan banding.</p>
        </a>
    </div>
</x-metronic.card>
@endsection
