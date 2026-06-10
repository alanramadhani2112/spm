@extends('layouts.metronic.app')

@section('title', 'Master Data')
@section('pageTitle', 'Master Data')

@section('content')
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

<div class="row g-5 g-xl-8">
    @foreach([
        ['route' => 'superadmin.master-data.edpm.index', 'title' => 'Master EDPM', 'desc' => 'Kelola komponen dan butir instrumen EDPM/IPR.', 'icon' => 'ki-notepad-edit'],
        ['route' => 'superadmin.master-data.document-categories.index', 'title' => 'Kategori Dokumen', 'desc' => 'Kelola kategori dokumen dan kewajiban per fase.', 'icon' => 'ki-document'],
        ['route' => 'superadmin.master-data.roles.index', 'title' => 'Role & Permission', 'desc' => 'Atur permission yang diturunkan ke setiap role.', 'icon' => 'ki-security-user'],
        ['route' => 'superadmin.master-data.users.index', 'title' => 'Akun Pengguna', 'desc' => 'Kelola role dan status akun pengguna.', 'icon' => 'ki-profile-user'],
    ] as $item)
        <div class="col-md-6">
            <a href="{{ route($item['route']) }}" class="card card-flush h-100 bg-light text-decoration-none">
                <div class="card-body d-flex align-items-start gap-5 p-8">
                    <span class="symbol symbol-50px"><span class="symbol-label bg-primary"><i class="ki-outline {{ $item['icon'] }} fs-2 text-white"></i></span></span>
                    <div>
                        <h3 class="fs-5 fw-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                        <p class="fs-7 text-muted mb-0">{{ $item['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
