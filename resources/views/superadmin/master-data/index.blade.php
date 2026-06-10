@extends('layouts.metronic.app')

@section('title', 'Master Data')
@section('pageTitle', 'Master Data')

@section('content')
@php
    $totalMaster = array_sum($stats ?? []);
    $insightCards = [
        [
            'label' => 'Instrumen EDPM',
            'value' => ($stats['komponen'] ?? 0).' komponen · '.($stats['butir'] ?? 0).' butir',
            'desc' => 'Pastikan setiap komponen memiliki butir penilaian yang relevan.',
            'icon' => 'ki-notepad-edit',
            'color' => 'primary',
        ],
        [
            'label' => 'Aturan Dokumen',
            'value' => ($stats['dokumen'] ?? 0).' kategori',
            'desc' => 'Kategori dokumen mengatur kewajiban unggah dan akses per role.',
            'icon' => 'ki-document',
            'color' => 'warning',
        ],
        [
            'label' => 'Akses Sistem',
            'value' => ($stats['roles'] ?? 0).' role · '.($stats['users'] ?? 0).' akun',
            'desc' => 'Role, permission, dan status akun menjadi fondasi keamanan Super Admin.',
            'icon' => 'ki-security-user',
            'color' => 'success',
        ],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Control Center Master Data</h2>
        <p class="fs-7 text-muted mb-0">Kelola instrumen, dokumen, role, dan akun yang menjadi fondasi workflow akreditasi.</p>
    </div>
    <span class="badge badge-light-primary">{{ $totalMaster }} entri master</span>
</div>

<div class="row g-5 g-xl-8 mb-8">
    @foreach([
        ['label' => 'Komponen EDPM', 'value' => $stats['komponen'], 'icon' => 'ki-category', 'color' => 'primary'],
        ['label' => 'Butir EDPM', 'value' => $stats['butir'], 'icon' => 'ki-notepad-edit', 'color' => 'info'],
        ['label' => 'Kategori Dokumen', 'value' => $stats['dokumen'], 'icon' => 'ki-document', 'color' => 'warning'],
        ['label' => 'Role', 'value' => $stats['roles'], 'icon' => 'ki-security-user', 'color' => 'success'],
        ['label' => 'Akun', 'value' => $stats['users'], 'icon' => 'ki-profile-user', 'color' => 'danger'],
    ] as $stat)
        <div class="col-xl col-md-4">
            <x-metronic.stat-card :value="$stat['value']" :label="$stat['label']" :icon="$stat['icon']" :color="$stat['color']" />
        </div>
    @endforeach
</div>

<div class="row g-5 g-xl-8 mb-8">
    @foreach($insightCards as $insight)
        <div class="col-xl-4">
            <div class="card card-flush h-100 border border-dashed border-gray-300">
                <div class="card-body p-6">
                    <div class="d-flex align-items-start gap-4">
                        <span class="symbol symbol-45px flex-shrink-0">
                            <span class="symbol-label bg-light-{{ $insight['color'] }}"><i class="ki-outline {{ $insight['icon'] }} fs-2 text-{{ $insight['color'] }}"></i></span>
                        </span>
                        <div>
                            <div class="fw-bold text-gray-900 mb-1">{{ $insight['label'] }}</div>
                            <div class="fs-6 fw-bold text-{{ $insight['color'] }} mb-2">{{ $insight['value'] }}</div>
                            <div class="fs-8 text-muted">{{ $insight['desc'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-5 g-xl-8">
    @foreach([
        ['route' => 'superadmin.master-data.edpm.index', 'title' => 'Master EDPM', 'desc' => 'Kelola komponen dan butir instrumen EDPM/IPR.', 'icon' => 'ki-notepad-edit', 'color' => 'primary'],
        ['route' => 'superadmin.master-data.document-categories.index', 'title' => 'Kategori Dokumen', 'desc' => 'Kelola kategori dokumen dan kewajiban per fase.', 'icon' => 'ki-document', 'color' => 'warning'],
        ['route' => 'superadmin.master-data.roles.index', 'title' => 'Role & Permission', 'desc' => 'Atur permission yang diturunkan ke setiap role.', 'icon' => 'ki-security-user', 'color' => 'success'],
        ['route' => 'superadmin.master-data.users.index', 'title' => 'Akun Pengguna', 'desc' => 'Kelola role dan status akun pengguna.', 'icon' => 'ki-profile-user', 'color' => 'danger'],
    ] as $item)
        <div class="col-md-6">
            <a href="{{ route($item['route']) }}" class="card card-flush h-100 hover-elevate-up text-decoration-none border border-gray-200">
                <div class="card-body d-flex align-items-start justify-content-between gap-5 p-8">
                    <div class="d-flex align-items-start gap-5">
                        <span class="symbol symbol-50px flex-shrink-0"><span class="symbol-label bg-light-{{ $item['color'] }}"><i class="ki-outline {{ $item['icon'] }} fs-2 text-{{ $item['color'] }}"></i></span></span>
                        <div>
                            <h3 class="fs-5 fw-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                            <p class="fs-7 text-muted mb-0">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                    <span class="btn btn-sm btn-light-{{ $item['color'] }} flex-shrink-0">Kelola</span>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
