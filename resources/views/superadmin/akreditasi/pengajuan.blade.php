@extends('layouts.metronic.app')

@section('title', 'Super Admin — Pengajuan Baru')
@section('pageTitle', 'Pengajuan Akreditasi Baru')

@section('toolbar')
<a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
@endsection

@section('breadcrumbs')
    <x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Konsol Akreditasi', 'route' => 'superadmin.akreditasi.index'], ['label' => 'Pengajuan Baru', 'active' => true]]" />
@endsection

@section('content')
@php $totalEligible = count($eligible); $totalPending = count($pendingPrerequisites); $totalBlocked = count($hasActiveAkreditasi); $totalAll = $totalEligible + $totalPending + $totalBlocked; @endphp



<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        <p class="fs-7 text-muted mb-0">{{ $totalAll }} pesantren terdaftar · {{ $totalEligible }} siap diajukan · {{ $totalPending }} belum lengkap · {{ $totalBlocked }} sudah aktif</p>
    </div>
</div>

@if($totalAll === 0)
    <x-metronic.empty-state icon="ki-building-4" title="Belum ada pesantren terdaftar" description="Tambahkan pesantren melalui Master Data sebelum membuat pengajuan." :actionLabel="'Kelola Pengguna'" :actionRoute="route('superadmin.master-data.users.index')" />
@else
    <div class="card card-flush bg-light-info border border-info border-dashed mb-8">
        <div class="card-body p-6">
            <div class="d-flex align-items-start gap-4">
                <span class="symbol symbol-40px"><span class="symbol-label bg-info"><i class="ki-outline ki-information-2 fs-3 text-white"></i></span></span>
                <div>
                    <div class="fw-bold text-gray-900 mb-2">Prasyarat Pengajuan</div>
                    <p class="fs-7 text-muted mb-0">Profil pesantren minimal harus lengkap (nama, NSPP, alamat) dan modul IPM, SDM, EDPM sudah diisi. Setelah pengajuan, profil dikunci dan status masuk ke <strong>Review Awal</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    @if(count($eligible))
    <x-metronic.card title="Langkah 1 — Pilih Pesantren ({{ $totalEligible }} siap)">
        <form method="POST" action="{{ route('superadmin.akreditasi.submit-pengajuan') }}" data-swal-confirm="true" data-swal-title="Konfirmasi pengajuan?" data-swal-text="Profil pesantren akan dikunci dan tidak dapat diubah. Lanjutkan?" data-swal-icon="question" data-swal-confirm-button="Ya, ajukan" data-swal-confirm-class="btn btn-primary">
            @csrf
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead><tr class="text-gray-400 fw-bold fs-7 text-uppercase"><th class="w-25px"></th><th>Pesantren</th><th>NSPP</th><th>Status Data</th></tr></thead>
                    <tbody>
                        @foreach($eligible as $entry)
                            <tr>
                                <td><div class="form-check"><input class="form-check-input" type="radio" name="pesantren_id" value="{{ $entry['id'] }}" required></div></td>
                                <td><div class="fw-bold text-gray-900">{{ $entry['name'] }}</div><span class="text-muted fs-7">{{ $entry['email'] }}</span></td>
                                <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                                <td><span class="badge badge-light-success">Lengkap</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-send fs-4"></i>Ajukan Akreditasi</button>
            </div>
        </form>
    </x-metronic.card>
    @endif

    @if(count($pendingPrerequisites))
    <x-metronic.card title="Langkah 2 — Lengkapi Data ({{ $totalPending }} belum lengkap)" class="mt-6">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead><tr class="text-gray-400 fw-bold fs-7 text-uppercase"><th>Pesantren</th><th>NSPP</th><th>Data Kurang</th><th></th></tr></thead>
                <tbody>
                    @foreach($pendingPrerequisites as $entry)
                        @php $missing = $entry['missing'] ?? []; @endphp
                        <tr>
                            <td><div class="fw-bold text-gray-900">{{ $entry['name'] }}</div><span class="text-muted fs-7">{{ $entry['email'] }}</span></td>
                            <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                            <td>@foreach($missing as $m)<span class="badge badge-light-danger me-1">{{ $m }}</span>@endforeach</td>
                            <td><a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-sm btn-light-warning">Lengkapi</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-metronic.card>
    @endif

    @if(count($hasActiveAkreditasi))
    <x-metronic.card title="Langkah 3 — Sudah Aktif ({{ $totalBlocked }} dalam proses)" class="mt-6">
        <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex align-items-center gap-3 p-4 mb-4">
            <i class="ki-outline ki-information-2 fs-2 text-danger"></i>
            <div class="fs-7 text-gray-700">Pesantren berikut masih memiliki pengajuan aktif. Selesaikan atau batalkan terlebih dahulu.</div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead><tr class="text-gray-400 fw-bold fs-7 text-uppercase"><th>Pesantren</th><th>NSPP</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($hasActiveAkreditasi as $entry)
                        <tr>
                            <td><div class="fw-bold text-gray-900">{{ $entry['name'] }}</div><span class="text-muted fs-7">{{ $entry['email'] }}</span></td>
                            <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                            <td><span class="badge badge-light-danger">{{ $entry['active_akreditasi_label'] }}</span></td>
                            <td><a href="{{ route('superadmin.akreditasi.show', $entry['active_akreditasi_id']) }}" class="btn btn-sm btn-light">Lihat</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-metronic.card>
    @endif
@endif
@endsection

