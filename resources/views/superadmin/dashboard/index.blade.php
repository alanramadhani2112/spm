@extends('layouts.metronic.app')

@section('title', 'Super Admin Dashboard')
@section('pageTitle', 'Dashboard Super Admin')

@section('toolbar')
<form method="GET" action="{{ route('superadmin.dashboard') }}" class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <label for="dashboard_period" class="visually-hidden">Periode dashboard</label>
    <select id="dashboard_period" name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
        @foreach(($periodOptions ?? ['all' => 'Semua Periode']) as $value => $label)
            <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if(auth()->user()?->hasPermission('superadmin.dashboard.export'))
        <a href="{{ route('superadmin.dashboard.export', ['period' => $period ?? 'all']) }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-exit-up fs-2"></i>Ekspor CSV
        </a>
    @endif
</form>
@endsection

@section('breadcrumbs')
    <x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'active' => true]]" />
@endsection

@section('content')
    

@php
    $completionRate = $totalAkreditasi > 0 ? round(($completedAkreditasi / $totalAkreditasi) * 100) : 0;
    $stepActions = [
        1 => ['action' => 'Review dokumen awal, terima atau tolak pengajuan', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'initial_submitted']],
        2 => ['action' => 'Pantau progres assessment, pastikan sebelum deadline', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'assessment_open']],
        3 => ['action' => 'Review administrasi, minta perbaikan atau approve', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'admin_stage_1_review']],
        4 => ['action' => 'Tentukan ketua dan anggota asesor', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'assessor_assignment']],
        5 => ['action' => 'Pantau koreksi dari pesantren', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'admin_stage_1_correction']],
        6 => ['action' => 'Jadwalkan visitasi, pantau pelaksanaan', 'route' => 'superadmin.visitasi.index', 'routeParams' => []],
        7 => ['action' => 'Input NA1/NA2/NK, upload laporan', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'post_visitasi_scoring,visitasi_result_submitted']],
        8 => ['action' => 'Validasi akhir, NV override, approve/tolak', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'admin_final_validation']],
        9 => ['action' => 'Terbitkan SK, proses banding', 'route' => 'superadmin.sk.index', 'routeParams' => ['status' => 'ready']],
        10 => ['action' => 'Akreditasi selesai — lihat arsip', 'route' => 'superadmin.akreditasi.index', 'routeParams' => ['status' => 'completed']],
    ];
    $firstActionable = null;
    foreach ($pipelineSteps as $step) {
        if (($step['count'] ?? 0) > 0 && $firstActionable === null) {
            $firstActionable = $step;
        }
    }
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div class="mw-lg-600px">
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Dashboard Super Admin</h2>
        <p class="fs-7 text-muted mb-3">Alur proses bisnis akreditasi end-to-end — mulai dari langkah pertama yang perlu dikerjakan.</p>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-light-primary">{{ $totalAkreditasi }} total pengajuan</span>
            <span class="badge badge-light-warning">{{ $overdueCount }} overdue</span>
            <span class="badge badge-light-success">{{ $completedAkreditasi }} selesai</span>
            @if(($period ?? 'all') !== 'all')
                <span class="badge badge-light-info">Periode: {{ $period }}</span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-primary">
            <i class="ki-outline ki-document fs-3"></i>Workflow Console
        </a>
        <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-light-warning">
            <i class="ki-outline ki-medal-star fs-3"></i>SK Siap Terbit
        </a>
        <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-setting-2 fs-3"></i>Master Data
        </a>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $totalAkreditasi }}" label="Total Akreditasi" icon="ki-document" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $activeAkreditasi }}" label="Sedang Berjalan" icon="ki-timer" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $completedAkreditasi }}" label="Selesai" icon="ki-shield-tick" color="success" progress="{{ $completionRate }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $overdueCount }}" label="Overdue" icon="ki-warning" color="danger" />
    </div>
</div>

@if($firstActionable)
<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-6">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-flag fs-2 text-white"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Mulai dari: {{ $firstActionable['label'] }}</h3>
                    <p class="fs-7 text-muted mb-0">{{ $stepActions[$firstActionable['id']]['action'] ?? '' }} — {{ $firstActionable['count'] }} pengajuan menunggu.</p>
                </div>
            </div>
            <a href="{{ route($stepActions[$firstActionable['id']]['route'] ?? 'superadmin.akreditasi.index', $stepActions[$firstActionable['id']]['routeParams'] ?? []) }}" class="btn btn-primary">
                <i class="ki-outline ki-right fs-3"></i>Kerjakan Sekarang
            </a>
        </div>
    </div>
</div>
@endif

<x-superadmin.akreditasi-pipeline :steps="$pipelineSteps" :currentIndex="$pipelineActiveIndex" :period="$period ?? 'all'" />

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-8">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Tindakan Diperlukan</h3>
                    <span class="text-muted fs-7 mt-1">Urutan prioritas berdasarkan alur proses bisnis — kerjakan dari atas ke bawah.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                @php $hasActionable = false; @endphp
                @foreach($pipelineSteps as $step)
                    @php $count = (int) ($step['count'] ?? 0); @endphp
                    @if($count > 0 && !empty($step['statusFilters']))
                        @php $hasActionable = true; $action = $stepActions[$step['id']] ?? null; @endphp
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 py-4 border-bottom border-gray-200">
                            <div class="d-flex align-items-center gap-4">
                                <span class="symbol symbol-35px flex-shrink-0">
                                    <span class="symbol-label bg-light-{{ $step['color'] ?? 'primary' }}">
                                        <span class="fw-bold fs-7 text-{{ $step['color'] ?? 'primary' }}">{{ $step['id'] }}</span>
                                    </span>
                                </span>
                                <div>
                                    <div class="fw-semibold text-gray-900">{{ $step['label'] }}</div>
                                    <div class="fs-8 text-muted">{{ $action['action'] ?? '' }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge badge-light-{{ $step['color'] ?? 'primary' }} fs-7">{{ $count }} pengajuan</span>
                                @if($action)
                                    <a href="{{ route($action['route'], $action['routeParams']) }}" class="btn btn-sm btn-light-{{ $step['color'] ?? 'primary' }}">
                                        <i class="ki-outline ki-right fs-4"></i>Lihat
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hasActionable)
                    <div class="text-center py-12 text-muted">
                        <div class="fw-bold text-gray-900 mb-2">Tidak ada tindakan yang diperlukan saat ini.</div>
                        <p class="fs-7">Semua pengajuan sudah selesai atau belum ada pengajuan baru untuk periode ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Aktivitas Terbaru</h3>
                    <span class="text-muted fs-7 mt-1">Pengajuan terakhir pada periode terpilih.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-8">
                    <div class="d-flex justify-content-between fw-semibold fs-7 mb-2">
                        <span>Completion Rate</span>
                        <span class="text-primary">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress h-8px">
                        <div class="progress-bar bg-primary" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>

                <div class="d-grid gap-5">
                    @forelse($recentAkreditasis as $akreditasi)
                        @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; @endphp
                        <div class="d-flex align-items-start gap-4">
                            <div class="symbol symbol-35px flex-shrink-0">
                                <span class="symbol-label bg-light-{{ $color }}"><i class="ki-outline ki-document fs-5 text-{{ $color }}"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <span class="badge badge-light-{{ $color }}">{{ $akreditasi->getStatusLabel() }}</span>
                                    <span class="fs-8 text-muted">{{ $akreditasi->created_at?->format('d M Y') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="btn btn-sm btn-icon btn-light" aria-label="Lihat detail akreditasi {{ $akreditasi->uuid }}">
                                <i class="ki-outline ki-right fs-3"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-12 text-muted border rounded bg-light">Belum ada aktivitas akreditasi untuk periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


